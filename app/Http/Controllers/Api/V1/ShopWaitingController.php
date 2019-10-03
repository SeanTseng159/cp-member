<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Logger;
use App\Enum\WaitingStatus;
use App\Result\ShopWaitingResult;
use App\Services\ShopWaitingService;
use App\Services\Ticket\MemberDiningCarService;
use App\Traits\MemberHelper;
use App\Traits\ShopHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class ShopWaitingController extends RestLaravelController
{
    use MemberHelper;
    use ShopHelper;

    private $service;
    private $memberDiningCarService;

    public function __construct(ShopWaitingService $service, MemberDiningCarService $memberDiningCarService)
    {
        $this->service = $service;
        $this->memberDiningCarService = $memberDiningCarService;
    }

    public function info(Request $request, $shopId)
    {
        try {
            $waiting = $this->service->find($shopId);
            $data = (new ShopWaitingResult())->info($waiting);
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::info', $e->getMessage());
            return $this->failureCode('E0001', $e->getMessage());
        }
    }

    public function create(Request $request, $shopId)
    {

        try {

            $validator = \Validator::make(
                $request->only([
                    'name',
                    'number',
                    'cellphone'
                ]),
                [
                    'name' => 'required',
                    'number' => 'required|integer',
                    'cellphone' => 'required'
                ]
            );
            if ($validator->fails()) {
                throw new \Exception($validator->messages());
            }

            $waiting = $this->service->find($shopId);


            if (is_null($waiting))
                throw new \Exception('查無此店鋪');

            if (!$waiting->canWaiting) {
                throw new \Exception('商家不開放候位');
            }

            //沒有設定候位資訊
            if (is_null($waiting->waitingSetting)) {
                throw new \Exception('無法候位，尚未設定候位資訊');
            }

            $isOpen = false;
            //檢查今天是否有營業
            $todayBusiness = $waiting->businessHoursDays
                ->filter(function ($day) {
                    $dayofWeek = Carbon::now()->dayOfWeekIso;
                    return $day->day == $dayofWeek && ($day->status == 1);
                });

            //檢查現在是否在今天的營業時間內
            if ($todayBusiness) {
                $times = $todayBusiness->first()->times;
                $todayBusinessTimes = $times->filter(function ($time) {
                    $start = explode(":", $time->start_time);
                    $end = explode(":", $time->end_time);
                    $startTime = Carbon::create(Carbon::now()->year, Carbon::now()->month, Carbon::now()->day, $start[0], $start[1]);
                    $endTime = Carbon::create(Carbon::now()->year, Carbon::now()->month, Carbon::now()->day, $end[0], $end[1]);
                    return Carbon::now()->between($startTime, $endTime) && ($time->status == 1);
                });
                if (count($todayBusinessTimes) > 0)
                    $isOpen = true;
            }

            //尚未開始營業
            if (!$isOpen) {
                throw new \Exception('非營業時間，無法候位');
            }

            $name = $request->input('name');
            $number = $request->input('number');
            $cellphone = $request->input('cellphone');

            //超過可候位人數
            $maxCapacity = $waiting->waitingSetting->capacity;
            if ($number > $maxCapacity) {
                throw new \Exception("僅提供人數{$maxCapacity}位內的候位");
            }

            $currentNo = $this->getCurrentWaitingNo($waiting);


            $memberID = $this->getMemberId();
            $record = $this->service->create($shopId, $name, $number, $cellphone, $memberID);

            $host = env("CITY_PASS_WEB");
            $shopName = $waiting->name;
            $userName = $record->name;


            $userWaitingNo = $this->getWaitNoString($record->waiting_no);
            //傳送簡訊認證
            $this->service->sendWaitingSMS($host, $shopName, $userName, $cellphone, $userWaitingNo, $record->code);
            //取得候位組數
            $count = $this->service->getWaitingNumber($shopId, $record->waiting_no);

            $data = new \stdClass();
            $data->id = $record->id;
            $data->name = $userName;
            $data->cellphone = $record->cellphone;
            $data->number = $record->number;
            $data->waitingNo = $this->getWaitNoString($record->waiting_no);
            $data->currentNo = $currentNo;
            $data->code = $record->code;
            $data->WaitingNum = $count;
            $data->status = $record->status;
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::create', $e->getMessage());
            return $this->responseFormat($data = null, $code = 'E0001', $message = $e->getMessage());
        }

    }

    public function get(Request $request, $shopId, $waitingId)
    {
        try {
            $waiting = $this->service->find($shopId);
            $currentNo = $this->getCurrentWaitingNo($waiting);

            $record = $this->service->get($shopId, $waitingId);
            $ret = (new ShopWaitingResult())->get($record);
            $isToday = $record->date == Carbon::now()->format('Y-m-d');
            if ($isToday) {
                $ret->WaitingNum = $this->service->getWaitingNumber($shopId, $record->waiting_no);
                $ret->currentNo = $currentNo;
            }
            return $this->success($ret);
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::get', $e->getMessage());
            return $this->failureCode('E0007', $e->getMessage());
        }


    }

    public function getByCode(Request $request, $code)
    {
        try {

            $record = $this->service->getByCode($code);
            $ret = (new ShopWaitingResult())->get($record);

            $isToday = $record->date == Carbon::now()->format('Y-m-d');
            if ($isToday) {
                $waiting = $this->service->find($record->shop->id);
                $currentNo = $this->getCurrentWaitingNo($waiting);

                $ret->WaitingNum = $this->service->getWaitingNumber($record->dining_car_id, $record->waiting_no);
                $ret->currentNo = $currentNo;
            }
            return $this->success($ret);
        } catch (\Exception $e) {

            Logger::error('ShopWaitingController::get', $e->getMessage());
            return $this->failureCode('E0007', $e->getMessage());
        }


    }

    public function deleteByCode(Request $request, $code)
    {
        try {
            $result = $this->service->deleteByCode($code);
            return $this->success();
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::deleteByCode', $e->getMessage());
            return $this->failureCode('E0004');
        }


    }


    /**取得目前叫號
     * @param $waiting
     * @return int
     */
    private function getCurrentWaitingNo($waiting): int
    {

        $onCallList = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::Called;
        });

        $currentNo = 0;
        if (count($onCallList) > 0) {
            $first = $onCallList->first();
            $currentNo = $first->waiting_no;
        }
        return $currentNo;
    }


    public function decode(Request $request, $code)
    {

        try {
            if (!$code)
                throw new \Exception('缺少$code');

            $data = $this->service->decode($code);
            if (!$data)
                throw new \Exception("查無候位記錄");

            $ret = new \stdClass();
            $ret->shop_id = $data->dining_car_id;
            $ret->waiting_id = $data->id;
            return $this->success($ret);
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::decode', $e->getMessage());
            return $this->failureCode('E0001', $e->getMessage());
        }
    }

    public function memberList(Request $request)
    {
        try {

            $memberId = $request->memberId;
            // 取收藏列表
            $memberDiningCars = $this->memberDiningCarService->getAllByMemberId($memberId);

            //後位清單
            $data = $this->service->getMemberList($memberId);
            $result = (new ShopWaitingResult())->memberList($data, $memberDiningCars);
            return $this->success($result);
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::memberList', $e->getMessage());
            return $this->failureCode('E0001', $e->getMessage());
        }
    }
}