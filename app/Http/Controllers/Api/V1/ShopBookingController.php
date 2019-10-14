<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;
use App\Services\Ticket\ShopBookingService;
use App\Core\Logger;
use App\Result\Ticket\ShopBookingResult;
use App\Services\Ticket\MemberDiningCarService;
use App\Traits\MemberHelper;
use App\Services\MemberService;

class ShopBookingController extends RestLaravelController
{
    use MemberHelper;
    protected $shopBookingService;
    protected $service;
    protected $memberService;
    protected $memberDiningCarService;
    public function __construct(ShopBookingService $service, MemberDiningCarService $memberDiningCarService, MemberService $memberService)
    {
        $this->service = $service;
        $this->memberDiningCarService = $memberDiningCarService;
        $this->memberService=$memberService;
    }

    public function maxpeople(Request $request, $id){
        try {
            $bookingLimit = $this->service->findBookingLimit($id);
            $data = (new ShopBookingResult())->maxpeople($bookingLimit);
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('ShopBookingController::maxpeople', $e->getMessage());
            return $this->failureCode('E0001');
        }
    }


    public function findBookingCanDate(Request $request, $id){
        try{
            $bookingNumOfPeo=$request['number'];
            $bookingLimit = $this->service->findBookingLimit($id);
            $bookingDateBooked = $this->service->findBookingDateBooked($id);
            $bookingDateTimes = $this->service->findBookingDateTimes($id);
            //將資料給result處理吧
            $data = (new ShopBookingResult())->findBookingCanDate($bookingLimit,$bookingDateBooked,$bookingDateTimes,$bookingNumOfPeo);
            return $this->success($data);
        }catch (\Exception $e) {
            Logger::error('ShopBookingController::findBookingCanDate', $e->getMessage());
            return $this->failureCode('E0001');
        }
    }

    public function finishedBooking(Request $request, $id){      
        $memberID=$this->getMemberId();
        try {
            $validator = \Validator::make(
                $request->only([
                    'name',
                    'phone',
                    'demand',
                    'people',
                    'date',
                    'time',
                    'dayOfWeek',
                ]),
                [
                    'name' => 'required',
                    'phone' => 'required',
                    'date' => 'required',
                    'time' => 'required',
                    'dayOfWeek' => 'required',
                    'people'=> 'required'
                ]);
            if ($validator->fails()) {
                throw new \Exception($validator->messages());
            }
            

            //抓取星期幾幾點多少人之限制
            $bookingTimesDateTime = $this->service->findBookingTimesDateTime($id,$request->input('dayOfWeek'),$request->input('time'));

            //抓取那天訂位的人數
            $bookedDateTime = $this->service->findBookedDateTime($id,$request->input('date'),$request->input('time'));
            //抓取目前的訂位編號
            $bookedNumber = $this->service->findBookedNumber($id);
            //抓取店家資料
            $shopInfo=$this->service->findShopInfo($id);
            //將資料給result處理吧
            $data = (new ShopBookingResult())->finishedBooking($bookingTimesDateTime,$bookedDateTime,$bookedNumber,$shopInfo,$request, $id,$memberID);
            
            //將資料寫入DB吧,True 寫入DB
            if($data->status){
                $record = $this->service->createDetail($data);
            }else{
                throw new \Exception('已額滿，請重新定位');
            }//end if
            $host = env("CITY_PASS_WEB");
            $datetime=$data->booking->date.' '.$data->booking->time;
            //傳送簡訊認證
            $this->service->sendBookingSMS($host, $data->shop->name, $data->member->name, $data->member->phone, $datetime, $data->booking->code);
            if (empty($memberID)){
            }else{    
                $member=$this->memberService->find($memberID);
            // //傳送EMAIL
                $this->service->sendBookingEmail($member,$data); 
            }//endif
            

            return $this->success($data);

        }catch (\Exception $e) {
            Logger::error('ShopBookingController::finishedBooking', $e->getMessage());
            return $this->responseFormat($data = null, $code = 'E0001', $message = $e->getMessage());
        }//end try   
    }//end public function finishedBooking


    public function getOenDetailInfo(Request $request, $shopId,$id){

        try{
        //抓取店家資料
            $shopInfo=$this->service->findShopInfo($shopId);
        //抓取單一訂位資料 
            $dataDetailInfo=$this->service->getOenDetailInfo($id);
        //將資料給result處理吧
            $data = (new ShopBookingResult())->getOenDetailInfo($shopInfo,$dataDetailInfo);
            return $this->success($data);
        }catch (\Exception $e) {
            Logger::error('ShopBookingController::getOenDetailInfo', $e->getMessage());
            return $this->responseFormat($data = null, $code = 'E0001', $message = $e->getMessage());
        }//end try 
    }//end public function getOenDetailInfo


    //訂位短網址解碼
    public function get(Request $request, $code){
        $data=$this->service->getFromCode($code);
        try{
        //封裝到result送出去
            $result=new \stdClass;
            $result->booking_id  =$data->id;
            $result->shop_id     =$data->shop_id;
            return $this->success($result);
        }catch (\Exception $e) {
            Logger::error('ShopBookingController::getOenDetailInfo', $e->getMessage());
            return $this->responseFormat($data = null, $code = 'E0001', $message = '無效的shop的code');
        }//end try
    }//end function get

    //取消訂位
    public function delete(Request $request, $shopid,$code){
        try{
             $result=$this->service->cancel($shopid,$code);
            return $this->success();
        } catch (\Exception $e) {
            Logger::error('ShopBookingController::delete', $e->getMessage());
            return $this->failureCode('E0004');
        }//en try
        
    }//end public function delete

    public function memberList(Request $request)
    {
        
        // try {
            $memberId = $request->memberId;
            // 取收藏列表
            $memberDiningCars = $this->memberDiningCarService->getAllByMemberId($memberId);
            //後位清單
            $data = $this->service->getMemberList($memberId);
            
            
            $result = (new ShopBookingResult())->memberList($data, $memberDiningCars);
            return $this->success($result);
        // } catch (\Exception $e) {
        //     Logger::error('ShopWaitingController::memberList', $e->getMessage());
        //     return $this->failureCode('E0001', $e->getMessage());
        // }
    }//end public function memberList

}//end class



