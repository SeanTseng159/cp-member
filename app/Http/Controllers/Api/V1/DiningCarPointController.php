<?php


namespace App\Http\Controllers\Api\V1;


use App\Core\Logger;
use App\Exceptions\ErrorCode;
use App\Result\Ticket\DiningCarPointResult;
use App\Result\Ticket\DiningCarResult;
use App\Services\Ticket\DiningCarPointRecordService;
use App\Services\Ticket\GiftService;
use App\Services\Ticket\MemberGiftItemService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use mysql_xdevapi\Exception;

class DiningCarPointController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $diningCarPointRecordService;
    protected $giftService;
    protected $memberGiftItemService;


    public function __construct(DiningCarPointRecordService $diningCarPointRecordService, GiftService $giftService,
                                MemberGiftItemService $memberGiftItemService)
    {
        $this->diningCarPointRecordService = $diningCarPointRecordService;
        $this->giftService = $giftService;
        $this->memberGiftItemService = $memberGiftItemService;
    }


    public function total(Request $request, $diningCarID)
    {
        try {
            $memberId = $request->memberId;
            $point = $this->diningCarPointRecordService->total($diningCarID, $memberId);

            return $this->success(['point' => $point]);
        } catch (\Exception $e) {
            Logger::error('point total Error', $e->getMessage());
            return $this->failureCode('E0001');
        }
    }

    public function exchange(Request $request, $giftId)
    {
        try {
            $memberId = $request->memberId;
            $exchangeQty = $request->input('qty', 0);
            if ($exchangeQty <= 0) {
                throw new \Exception('E0001');
            }

            //取得gift的兌換點數
            $gift = $this->giftService->getWithDiningCar($giftId);


            if (!$gift) {
                throw new \Exception('E0076');
            }

            //檢查是否有足夠的點數可以兌換
            $memberTotalPoint = $this->diningCarPointRecordService->total($gift->model_spec_id, $memberId);

            $exchangePoint = $gift->points * $exchangeQty;

            if ($memberTotalPoint < $exchangePoint) {
                throw new \Exception('E0077');
            }

            //檢查是否還有額度可兌換
            $mememberGiftStatus = $this->memberGiftItemService->getUsedCount([$giftId]);

            $giftID = $gift->id;
            $qty = $gift->qty;
            $limiQty = $gift->limit_qty;

            $gift->status = 0;

            $usedAll = $mememberGiftStatus->where('gift_id', $giftID)->sum('total');

            //全部額度已用完
            if ($usedAll >= $qty) {
                throw new \Exception('E0078');
            }

            //個人額度已用完
            $personalUsed = $mememberGiftStatus->where('member_id', $memberId)->sum('total');

            if ($personalUsed >= $limiQty) {
                throw new \Exception('E0078');
            }

            //若可兌換張數不足，已超過全額或個人額度
//            6 + 2 > 7 or 4+ 2> 6

            if (($usedAll + $exchangeQty > $qty) or ($personalUsed + $exchangeQty > $limiQty)) {
                $maxExchangeQty =
                    ($qty - $usedAll) < ($limiQty - $personalUsed) ?
                        ($qty - $usedAll) :
                        ($limiQty - $personalUsed);

                //格式特殊，直接丟回
                return $this->responseFormat(
                    ['maxExchangeQty' => $maxExchangeQty],
                    'E0079',
                    ErrorCode::message('E0079'));

            }

            //寫入DB dining_car_point_records & member_gift_items//
            $diningCarId = $gift->model_spec_id;
            $expireTime = $gift->expire_at;
            $ret = $this->diningCarPointRecordService->create(
                $memberId,
                $diningCarId,
                $exchangePoint,
                $expireTime,
                $gift->id,
                $exchangeQty);

            if (!$ret) {
                throw  new \Exception('E0002');
            }

            //兌換後禮物的狀態
            $status = 0;
            if (($usedAll + $exchangeQty >= $qty) or ($personalUsed + $exchangeQty >= $limiQty)) {
                $status = 1;
            }
            return $this->success(['status' => $status]);


        } catch (\Exception $e) {
            $code = 'E0001';
            if ($e->getMessage()) {
                $code = $e->getMessage();
            }

            return $this->failureCode($code);

        }


    }


    public function list(Request $request)
    {

//        try {

            $memberId = $request->memberId;
            $type = $request->input('type', 'give');
            $status = 0;

            $allowType = [1 => 'give', 2 => 'exchange'];

            $status = array_search($type, $allowType);

            if (!$status) {
                throw New \Exception('E0001');
            }

            $result = $this->diningCarPointRecordService->getPointRecord($status, $memberId);

            $data = (new DiningCarPointResult())->list($result);

            return $this->success($data);
//        } catch (\Exception $e) {
//            Logger::error('error in DiningCarPointController.list', $e->getMessage());
//            return $this->failureCode('E0001');
//        }


    }


}


