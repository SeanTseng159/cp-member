<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Http\Controllers\Api\V1;

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
use App\Services\MemberService;
use App\Services\Ticket\GiftService;

use App\Parameter\Ticket\DiningCarMemberParameter;
use App\Result\Ticket\DiningCarMemberResult;
use App\Result\Ticket\GiftResult;

use App\Jobs\DiningCar\ConsumeAmountExchangePoint;

class DiningCarMemberController extends RestLaravelController
{
    use CryptHelper;

    protected $service;
    protected $giftService;
    protected $memberCouponService;
    protected $memberGiftItemService;

    public function __construct(DiningCarMemberService $service,
                                GiftService $giftService,
                                MemberCouponService $memberCouponService,
                                MemberGiftItemService $memberGiftItemService
    )
    {
        $this->service = $service;
        $this->giftService = $giftService;
        $this->memberCouponService = $memberCouponService;
        $this->memberGiftItemService = $memberGiftItemService;
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

            // 發送禮物
            $gift = $this->giftService->giveAddDiningCarMemberGift($diningCarId, $memberId);
            $gift = (new GiftResult)->detailByJoinDiningCar($gift);

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
    public function invite(Request $request, DiningCarService $diningCarService)
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

            // 發送禮物
            $gift = $this->giftService->giveAddDiningCarMemberGift($diningCarId, $params['memberId']);
            $gift = (new GiftResult)->detailByJoinDiningCar($gift);

            // 發送點數
            $consumeAmount = (new Hashids('DiningCarConsumeAmount', 16))->decode($params['consumeAmount']);
            if ($consumeAmount && $consumeAmount[0] > 0) {
                $key = 'invite' . $member->id;
                dispatch(new ConsumeAmountExchangePoint($member, $consumeAmount[0], $key))->delay(5);
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
            $total = $couponNum + $giftNum;

            return $this->success([
                'coupon_num' => $couponNum,
                'gift_num' => $giftNum,
                'total' => $total
            ]);
        } catch (Exception $e) {
            return $this->failureCode('E0001');
        }

    }
}
