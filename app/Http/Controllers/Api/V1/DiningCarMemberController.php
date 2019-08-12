<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Http\Controllers\Api\V1;

use App\Services\AwardRecordService;
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

    public function __construct(DiningCarMemberService $service,
                                DiningCarService $diningCarService, 
                                DiningCarPointService $diningCarPointService,
                                GiftService $giftService,
                                FCMService $fcmService,
                                MemberCouponService $memberCouponService,
                                MemberGiftItemService $memberGiftItemService,
                                AwardRecordService $awardRecordService,
                                MemberNoticService $memberNoticService

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
    }

    /**
     * 加入餐車會員
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        try {
            $memberId = $request->memberId;
            $diningCarId = $request->input('diningCarId');

            if (!$diningCarId) return $this->failureCode('E0201');
            $diningCarId = $this->decryptHashId('DiningCar', $diningCarId);

            // 是否加入會員
            $isMember = $this->service->isMember($memberId, $diningCarId);
            if ($isMember) return $this->failureCode('A0101');

            $result = $this->service->add($memberId, $diningCarId);
            //加入餐車推播
            $memberIds[0] = $memberId;
            $data['url'] = CommonHelper::getWebHost('zh-TW/diningCar/detail/' . $diningCarId);
            $data['prodType'] = 5;
            $data['prodId'] = $diningCarId;
            $data['diningCarName'] = $this->diningCarService->find($diningCarId)->name;
            //$this->fcmService->memberNotify('addMember',$memberIds,$data);
            dispatch(new FCMSendPush('addMember',$memberIds,$data));

            //發送禮物
            $gift = $this->giftService->giveAddDiningCarMemberGift($diningCarId, $memberId);
            $gift = (new GiftResult)->detailByJoinDiningCar($gift);
            //發送禮物推播
            if($gift)
            {
                $data['url'] = CommonHelper::getWebHost('zh-TW/diningCar/detail/' . $diningCarId);
                $data['prodType'] = 6;
                $data['prodId'] = $diningCarId;
                $data['giftName'] = $gift->name;
                //$this->fcmService->memberNotify('addGift',$memberIds,$data);
                dispatch(new FCMSendPush('addGift',$memberIds,$data));
            }

            // 取會員卡資料
            $diningCarMember = $this->service->find($memberId, $diningCarId);
            $memberCard = (new DiningCarMemberResult)->getMemberCard($diningCarMember);

            return ($result) ? $this->success([
                'gift' => $gift,
                'memberCard' => $memberCard
            ]) : $this->failureCode('E0200');
        } catch (Exception $e) {
            return $this->failureCode('E0200');
        }
    }

    /**
     * 取使用者已加入會員的餐車
     * @param Request $request
     * @return JsonResponse
     */
    public function diningCars(Request $request)
    {
        try {
            $params = (new DiningCarMemberParameter($request))->list();

            $data = $this->service->list($request->memberId, $params);

            $result['page'] = (int)$params['page'];
            $result['total'] = $data->total();
            $result['cars'] = (new DiningCarMemberResult)->list($data);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 餐車&會員資料
     * @param Request $request
     * @param DiningCarService $diningCarService
     * @param MemberService $memberService
     * @param string $id
     * @return JsonResponse
     */
    public function info(Request $request, DiningCarService $diningCarService, MemberService $memberService, $id = '')
    {
        try {
            $params = (new DiningCarMemberParameter($request))->info();

            $diningCarId = (new Hashids('DiningCar', 6))->decode($id);
            $memberToken = (new Hashids('Member', 12))->decode($params['token']);

            if (!$diningCarId || !$memberToken) return $this->failureCode('E0001');

            $isMember = $this->service->isMember($memberToken[0], $diningCarId[0]);
            if ($isMember) return $this->failureCode('A0101');

            $diningCar = $diningCarService->find($diningCarId[0]);
            $member = $memberService->find($memberToken[0]);

            return $this->success([
                'car' => [
                    'id' => $diningCar->id,
                    'hashId' => $this->encryptHashId('DiningCar', $diningCarId[0]),
                    'name' => $diningCar->name
                ],
                'member' => [
                    'id' => $member->id,
                    'name' => $member->name
                ]
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0007');
        }
    }

    /**
     * 餐車邀請加入會員
     * @param Request $request
     * @param DiningCarService $diningCarService
     * @return JsonResponse
     */
    public function invite(Request $request, DiningCarService $diningCarService, DiningCarPointService $diningCarPointService)
    {
        try {
            $params = (new DiningCarMemberParameter($request))->invite();
            $diningCarId = $this->decryptHashId('DiningCar', $params['diningCarId']);

            // 確認餐車是否付費
            $isPaid = $diningCarService->isPaid($diningCarId);
            if (!$isPaid) return $this->failureCode('E0201');

            // 是否加入會員
            $isMember = $this->service->isMember($params['memberId'], $diningCarId);
            if ($isMember) return $this->failureCode('A0101');

            // 加入會員
            $member = $this->service->add($params['memberId'], $diningCarId);
            if (!$member) return $this->failureCode('E0200');
            //加入餐車推播
             $addmemberCheck = true;
            // 發送禮物
            $gift = $this->giftService->giveAddDiningCarMemberGift($diningCarId, $params['memberId']);
            $gift = (new GiftResult)->detailByJoinDiningCar($gift);
            $giftCheck = false;
            $giftName = '';
            //禮物推播確認
            if($gift)
            {
                $giftCheck = true;
                $giftName = $gift->name;
            }

            // 發送點數
            $consumeAmount = (new Hashids('DiningCarConsumeAmount', 16))->decode($params['consumeAmount']);
            if ($consumeAmount && $consumeAmount[0] > 0) {
                $key = 'invite' . $member->id;
                $rule = $diningCarPointService->getExchangeRateRule($diningCarId);
                //dispatch(new ConsumeAmountExchangePoint($member, $consumeAmount[0], $key ,$diningCarId ,$rule))->delay(5);
                $diningCarName = $this->diningCarService->find($diningCarId)->name;
                dispatch(new ConsumeAmountExchangePoint($member, $consumeAmount[0], $key ,$diningCarId ,$rule ,$addmemberCheck ,$giftCheck ,$diningCarName ,$giftName))->delay(5);
            }


            // 取車餐資料
            $diningCar = $diningCarService->find($diningCarId);

            return $this->success([
                'car' => [
                    'id' => $diningCar->id,
                    'name' => $diningCar->name
                ],
                'gift' => $gift
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0200');
        }
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
            $noticNum = $this->memberNoticService->availableNotic($memberId);
            $total = $couponNum + $giftNum + $awardNum + $noticNum;

            return $this->success([
                'coupon_num' => $couponNum,
                'gift_num' => $giftNum + $awardNum,
                'notic_num' => $noticNum,
                'total' => $total
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0001');
        }

    }
}
