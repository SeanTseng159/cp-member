<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Http\Controllers\Api\V2;

use App\Services\AwardRecordService;
use App\Services\Ticket\InvitationService;
use App\Services\Ticket\MemberCouponService;
use App\Services\Ticket\MemberGiftItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;
use App\Traits\CryptHelper;
use Hashids\Hashids;

use App\Services\Ticket\DiningCarMemberService;
use App\Services\Ticket\DiningCarService;
use App\Services\Ticket\DiningCarPointService;
use App\Services\MemberService;
use App\Services\Ticket\GiftService;
use App\Services\Ticket\MemberNoticService;
use App\Services\FCMService;
use App\Services\Ticket\MemberDiningCarDiscountService;

use App\Parameter\Ticket\DiningCarMemberParameter;
use App\Result\Ticket\DiningCarMemberResult;
use App\Result\Ticket\GiftResult;

use App\Jobs\DiningCar\ConsumeAmountExchangePoint;
use App\Jobs\FCMSendPush;
use App\Helpers\CommonHelper;

class DiningCarMemberController extends RestLaravelController
{
    use CryptHelper;

    protected $service;
    protected $diningCarService;
    protected $diningCarPointService;
    protected $giftService;
    protected $fcmService;
    protected $memberCouponService;
    protected $memberGiftItemService;
    protected $awardRecordService;
    protected $memberNoticService;
    protected $invitationService;
    protected $memberDiningCarDiscountService;
    public function __construct(DiningCarMemberService $service,
                                DiningCarService $diningCarService, 
                                DiningCarPointService $diningCarPointService,
                                GiftService $giftService,
                                FCMService $fcmService,
                                MemberCouponService $memberCouponService,
                                MemberGiftItemService $memberGiftItemService,
                                AwardRecordService $awardRecordService,
                                MemberNoticService $memberNoticService,
                                InvitationService $invitationService,
                                MemberDiningCarDiscountService $memberDiningCarDiscountService

    )
    {
        $this->service = $service;
        $this->diningCarService = $diningCarService;
        $this->giftService = $giftService;
        $this->fcmService = $fcmService;
        $this->memberCouponService = $memberCouponService;
        $this->memberGiftItemService = $memberGiftItemService;
        $this->awardRecordService = $awardRecordService;
        $this->memberNoticService = $memberNoticService;
        $this->invitationService = $invitationService;
        $this->memberDiningCarDiscountService=$memberDiningCarDiscountService;
    }


    /**
     *  可使用禮物數、優惠卷 與 總和
     * @param Request $request
     * @return JsonResponse
     */
    public function tickets(Request $request)
    {
        try {
            $memberId = $request->memberId;

            $couponNum = $this->memberCouponService->availableCoupons($memberId);
            $giftNum = $this->memberGiftItemService->availableGifts($memberId);
            //獎品清單
            $awardNum = $this->awardRecordService->availableAward($memberId);
            //平台禮物券
            $promoteGiftNum = $this->invitationService->availablePromoteGift($memberId);
            //優化卷
            $discount = $this->memberDiningCarDiscountService->availableDiscount($memberId);


            $noticNum = $this->memberNoticService->availableNotic($memberId);
            $total = $couponNum + $giftNum + $awardNum + $noticNum+$discount+$promoteGiftNum;

            return $this->success([
                'gift_test' =>$giftNum,
                'coupon_test' =>$couponNum,
                'discount_test' =>$discount,
                'award_test' =>$awardNum,
                'promte_test' =>$promoteGiftNum,
                'gift_num' => $total-$noticNum,
                'notic_num' => $noticNum,
                'total' => $total
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0001');
        }

    }
}
