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
     * ??????????????????
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
            
            //??????????????????
            $type = Input::get('type', 0);
            //?????? ???????????? ????????????
            $allowStatus = ['current' => 1, 'used' => 2, 'expired' => 3];
            if (!array_key_exists($type, $allowStatus)) {
                throw  new \Exception('E0001');
            }
            $status = $allowStatus[$type];
            
            //??????????????????????????????
            $diningCarGift = $this->memberGiftItemService->list($status, $memberId);
            
            //??????
            $award = $this->awardRecordService->list($status, $memberId);
            
            //??????????????????
            $promoteGifts = $this->invitationService->list($status, $memberId);
            
            //?????????
            $memberCoupons = $this->memberCouponService->favoriteCouponList($memberId, $status);
            
            //?????????
            $discount = $this->memberDiningCarDiscountService->list($status,$memberId);
            
            
            //??????????????????
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


            //check ???????????????discount??????????????????
            $checkDiscount=$this->diningCarDiscountService->find($discountID);
            
            if(empty($checkDiscount)){
                throw  new \Exception('E0505');
            }

            //find if exit ???????????????????????????
            $checkOnlyOne=$this->memberDiningCarDiscountService->checkOnlyOne($discountID,$memberId);
            if(!empty($checkOnlyOne)){
                throw  new \Exception('E0504');
            }


            //count ????????????????????????????????????????????????????????????????????????
            $countCheck=$this->diningCarDiscountService->checkCount($discountID);
            if(is_null($countCheck['number'])){
                throw  new \Exception('E0503');
            }elseif($countCheck['count']->COUNT+1>$countCheck['number']->number){
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

            //check ???????????????discount?????????????????? ??????????????????????????????
            $allList=$this->diningCarDiscountService->listAll();

            //???????????????
            if(empty($allList[0])){
                $result=[];
            }else{
                $result=[];
                //?????????????????????
                foreach($allList as $key=>$value){
                    $data=new \stdClass();
                    $data->id =    $value->id;
                    $data->name=   $value->name;
                    $data->desc=   $value->desc;
                    $data->price=  $value->price;
                    $data->number= $value->number;
                    $data->start_at=Carbon::parse($value->start_at)->format('Y-m-d');
                    $data->end_at=Carbon::parse($value->end_at)->format('Y-m-d');
                    
                    //???img????????????
                    $data->image=CommonHelper::getBackendHost($value->image_path);
                    
                    //find if exit ????????????????????????????????????????????????
                    $checkOnlyOne=$this->memberDiningCarDiscountService->checkOnlyOne($value->id,$memberId);
                    //count ????????????????????????????????????????????????????????????????????????
                    $countCheck=$this->diningCarDiscountService->checkCount($value->id);
                    //????????? //??????????????????//???????????????????????????
                    if( !empty($checkOnlyOne) or $countCheck['count']->COUNT+1 > $countCheck['number']->number or ($value->start_at >= Carbon::today() )or ($value->end_at <=Carbon::today()) ){
                        $data->status=false;
                    }//??????????????? //????????????
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
