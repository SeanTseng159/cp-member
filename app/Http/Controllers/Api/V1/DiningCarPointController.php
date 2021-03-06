<?php


namespace App\Http\Controllers\Api\V1;


use App\Core\Logger;
use App\Enum\ClientType;
use App\Exceptions\ErrorCode;
use App\Result\Ticket\DiningCarPointResult;
use App\Result\Ticket\DiningCarResult;
use App\Services\Ticket\DiningCarPointRecordService;
use App\Services\Ticket\GiftService;
use App\Services\Ticket\MemberGiftItemService;
use App\Services\FCMService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use mysql_xdevapi\Exception;
use App\Helpers\CommonHelper;
use App\Jobs\FCMSendPush;

class DiningCarPointController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $diningCarPointRecordService;
    protected $giftService;
    protected $memberGiftItemService;
    protected $fcmService;


    public function __construct(DiningCarPointRecordService $diningCarPointRecordService, 
                                GiftService $giftService,
                                MemberGiftItemService $memberGiftItemService,
                                FCMService $fcmService)
    {
        $this->diningCarPointRecordService = $diningCarPointRecordService;
        $this->giftService = $giftService;
        $this->memberGiftItemService = $memberGiftItemService;
        $this->fcmService = $fcmService;
    }


    public function total(Request $request, $diningCarID)
    {
        try {

            $memberId = $request->memberId;
            $point = $this->diningCarPointRecordService->total($diningCarID, $memberId);
            $gift = $this->memberGiftItemService->getUserAvailableGiftCount($memberId, ClientType::dining_car, $diningCarID);

            return $this->success([
                'point' => $point,
                'gift' => $gift
            ]);
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

            //??????gift???????????????
            $gift = $this->giftService->getWithDiningCar($giftId);


            if (!$gift) {
                throw new \Exception('E0076');
            }

            //??????????????????????????????????????????
            $memberTotalPoint = $this->diningCarPointRecordService->total($gift->model_spec_id, $memberId);

            $exchangePoint = $gift->points * $exchangeQty;

            if ($memberTotalPoint < $exchangePoint) {
                throw new \Exception('E0077');
            }

            //?????????????????????????????????
            $mememberGiftStatus = $this->memberGiftItemService->getUsedCount([$giftId]);

            $qty = $gift->qty;
            $limiQty = $gift->limit_qty;
            $gift->status = 0;


            //?????????????????????
            if ($qty <= 0) {
                throw new \Exception('E0078');
            }

            //?????????????????????
            $personalUsed = $mememberGiftStatus->where('member_id', $memberId)->sum('total');
            if ($personalUsed >= $limiQty) {
                throw new \Exception('E0078');
            }

            //???????????????
            $remainQty = $qty < ($limiQty - $personalUsed) ? $qty : ($limiQty - $personalUsed);

            if ($remainQty < $exchangeQty) {
                //???????????????????????????
                return $this->responseFormat(
                    ['maxExchangeQty' => $remainQty],
                    'E0079',
                    ErrorCode::message('E0079'));
            }


            //??????DB dining_car_point_records & member_gift_items &gift.qty
            $diningCarId = $gift->model_spec_id;
            $expireTime = Carbon::maxValue();//$gift->expire_at;
            $ret = $this->diningCarPointRecordService->create(
                $memberId,
                $diningCarId,
                $gift->points,
                $expireTime,
                $gift->id,
                $exchangeQty);
            //??????
            $data['url'] = CommonHelper::getWebHost('zh-TW/diningCar/detail/' . $diningCarId);
            $data['prodType'] = 6;
            $data['prodId'] = $diningCarId;
            $data['name'] = $gift->name;
            $data['point'] = $exchangePoint;
            $data['qty'] = $exchangeQty;
            $memberIds[0] = $memberId;
            //$this->fcmService->memberNotify('giftChange', $memberIds, $data);
            dispatch(new FCMSendPush('giftChange', $memberIds, $data));

            if (!$ret) {
                throw  new \Exception('E0002');
            }

            //????????????????????????????????????
            return $this->success([
                'qty' => $remainQty - $exchangeQty,
                'point' => ($memberTotalPoint - $exchangePoint)
            ]);


        } catch (\Exception $e) {
            $code = 'E0001';
            if ($e->getMessage()) {
                $code = $e->getMessage();
            }
            return $this->failureCode($code);
        }
    }


    public function list(Request $request, $diningCarID)
    {

        try {

            $memberId = $request->memberId;
            $type = $request->input('type', 'give');
            $status = 0;

            $allowType = [1 => 'give', 2 => 'exchange'];

            $status = array_search($type, $allowType);

            if (!$status) {
                throw New \Exception('E0001');
            }

            $result = $this->diningCarPointRecordService->getPointRecord($status, $memberId, $diningCarID);

            $data = (new DiningCarPointResult())->list($result);

            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('error in DiningCarPointController.list', $e->getMessage());
            return $this->failureCode('E0001');
        }


    }


}


