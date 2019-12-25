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

use Carbon\Carbon;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use stdClass;


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


    public function __construct(
        MemberGiftItemService $memberGiftItemService,
        ImageService $imageService,
        AwardRecordService $awardRecordService,
        InvitationService $invitationService,
        MemberCouponService $memberCouponService,
        MemberDiningCarDiscountService $memberDiningCarDiscountService
    )
    {

        $this->memberGiftItemService = $memberGiftItemService;
        $this->imageService = $imageService;
        $this->awardRecordService = $awardRecordService;
        $this->invitationService = $invitationService;
        $this->memberCouponService=$memberCouponService;
        $this->memberDiningCarDiscountService=$memberDiningCarDiscountService;
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


    }


    public function getDiscount(Request $request)
    {
        try{
                
                $discountID=$request->input('discountID');
                 //get member id from token
                $memberId = $request->memberId;
                
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

    }


}
