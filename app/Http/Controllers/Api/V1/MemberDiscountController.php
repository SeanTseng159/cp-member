<?php

namespace App\Http\Controllers\Api\V1;


use App\Enum\MyGiftType;
use App\Models\PromoteGift;
use App\Result\AwardRecordResult;
use App\Result\MemberGiftItemResult;
use App\Result\PromoteGiftRecordResult;
use App\Result\MemberCouponRecordResult;
use App\Result\MemberDiscountResult;

use App\Services\AwardRecordService;
use App\Services\ImageService;
use App\Services\Ticket\InvitationService;
use App\Services\Ticket\MemberGiftItemService;
use App\Services\Ticket\MemberCouponService;
use App\Services\Ticket\MemberDiningCarDiscountService;
use App\Services\Ticket\DiningCarDiscountService;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use stdClass;
use App\Helpers\CommonHelper;

class MemberDiscountController extends RestLaravelController
{
    const DelayVerifySecond = 90;
    protected $lang = 'zh-TW';
    protected $memberGiftItemService;
    protected $imageService;
    protected $awardRecordService;
    protected $invitationService;
    protected $memberDiningCarDiscountService;
    protected $memberCouponService;
    protected $qrCodePrefix = 'discount_';
    protected $diningCarDiscountService;


    public function __construct(
        MemberGiftItemService $memberGiftItemService,
        ImageService $imageService,
        AwardRecordService $awardRecordService,
        InvitationService $invitationService,
        MemberCouponService $memberCouponService,
        MemberDiningCarDiscountService $memberDiningCarDiscountService,
        DiningCarDiscountService $diningCarDiscountService
    )
    {

        $this->memberGiftItemService = $memberGiftItemService;
        $this->imageService = $imageService;
        $this->awardRecordService = $awardRecordService;
        $this->invitationService = $invitationService;
        $this->memberCouponService=$memberCouponService;
        $this->memberDiningCarDiscountService=$memberDiningCarDiscountService;
        $this->diningCarDiscountService=$diningCarDiscountService;
    }

    /**
     * 我的禮物列表
     *
     * @param Request $request
     *
     * @return JsonResponseAlias
     */
    public function list(Request $request)
    {
        try {
            //get member id from token
            $memberId = $request->memberId;
            
            //取得要得狀態
            $type = Input::get('type', 0);
            //有效 或者無效 得優惠卷
            $allowStatus = ['current' => 1, 'used' => 2, 'expired' => 3];
            if (!array_key_exists($type, $allowStatus)) {
                throw  new \Exception('E0001');
            }
            $status = $allowStatus[$type];
            
            //取得使用者的禮物清單
            $diningCarGift = $this->memberGiftItemService->list($status, $memberId);
            
            //獎品
            $award = $this->awardRecordService->list($status, $memberId);
            
            //邀請碼的獎品
            $promoteGifts = $this->invitationService->list($status, $memberId);
            
            //折價券
            $memberCoupons = $this->memberCouponService->favoriteCouponList($memberId, $status);
            
            //優惠卷
            $discount = $this->memberDiningCarDiscountService->list($status,$memberId);
            
            
            //各自整理格式
            $resultGift = (new MemberGiftItemResult())->list($diningCarGift, $status);
            $resultAward = (new AwardRecordResult())->list($award);
            $resultPromote = (new PromoteGiftRecordResult())->list($promoteGifts);
            $resultCoupons =(new MemberCouponRecordResult())->list($memberCoupons);
            $resultDiscount=(new MemberDiscountResult())->list($discount);
        
            $result = array_merge($resultGift, $resultAward, $resultPromote,$resultCoupons,$resultDiscount);
            $result = array_values(collect($result)->sortBy('duration')->toArray());
            
            return $this->success($result);

        } catch (\Exception $e) {
            if ($e->getMessage()) {
                return $this->failureCode($e->getMessage());
            }

            return $this->failureCode('E0007');
        }


    }//end list


    public function getDiscount(Request $request)
    {
        try{
                
            $discountID=$request->input('discountID');
             //get member id from token
            $memberId = $request->memberId;


            //check 是否有這張discount，且可以使用
            $checkDiscount=$this->diningCarDiscountService->find($discountID);
            
            if(empty($checkDiscount)){
                throw  new \Exception('E0505');
            }

            //find if exit ，每人僅能拿到一張
            $checkOnlyOne=$this->memberDiningCarDiscountService->checkOnlyOne($discountID,$memberId);
            if(!empty($checkOnlyOne)){
                throw  new \Exception('E0504');
            }


            //count 數量是否有超過發送，怕萬一有些問題多寫了一些判斷
            $countCheck=$this->diningCarDiscountService->checkCount($discountID);
            if(is_null($countCheck['number'])){
                throw  new \Exception('E0503');
            }elseif($countCheck['count']->COUNT>$countCheck['number']->number+1){
                throw  new \Exception('E0072');
            }



            //Qrcode make
            $duration = Carbon::now()->addSeconds($this::DelayVerifySecond)->timestamp;
            $qrcode=base64_encode("$memberId.$discountID.$duration");
            $code = $this->qrCodePrefix . $qrcode;

            $this->memberDiningCarDiscountService->createQrcode($memberId,$discountID,$qrcode);


            return $this->success();



        }catch (\Exception $e) {
            if ($e->getMessage()) {
                return $this->failureCode($e->getMessage());
            }

            return $this->failureCode('E0007');
        }

    }//end getDiscount



    public function discountList(Request $request)
    {
        try{
             //get member id from token
            $memberId = $request->memberId;

            //check 是否有這張discount，且可以使用
            $allList=$this->diningCarDiscountService->listAll();

            //活動結束了
            if(empty($allList[0])){
                $result=[];
            }else{
                $result=[];
                //一一取出值判斷
                foreach($allList as $key=>$value){
                    $data=new \stdClass();
                    $data->id =    $value->id;
                    $data->name=   $value->name;
                    $data->desc=   $value->desc;
                    $data->price=  $value->price;
                    $data->number= $value->number;
                    $data->start_at=Carbon::parse($value->start_at)->format('Y-m-d');
                    $data->end_at=Carbon::parse($value->end_at)->format('Y-m-d');
                    
                    //將img網址轉換
                    $data->image=CommonHelper::getBackendHost($value->image_path);
                    
                    //find if exit ，每人僅能拿到一張，是否有領取過
                    $checkOnlyOne=$this->memberDiningCarDiscountService->checkOnlyOne($value->id,$memberId);
                    //count 數量是否有超過發送，怕萬一有些問題多寫了一些判斷
                    $countCheck=$this->diningCarDiscountService->checkCount($value->id);
                    //裡取過 //是否領取過度
                    if(!empty($checkOnlyOne) or $countCheck['count']->COUNT > $countCheck['number']->number+1){
                        $data->status=false;
                    }//沒有領取過 //或可領取
                    else{
                        $data->status=true;
                    }//end if
                    $result[]=$data;
                }//end foreach
            }//end if empty
            
            return $this->success($result);
        }catch (\Exception $e) {
            if ($e->getMessage()) {
                return $this->failureCode($e->getMessage());
            }

            return $this->failureCode('E0007');
        }

    }//end discountList
}
