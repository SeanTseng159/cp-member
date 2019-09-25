<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Logger;
use App\Enum\WaitingStatus;
use App\Result\ShopWaitingResult;
use App\Services\ShopWaitingService;
use App\Traits\MemberHelper;
use function GuzzleHttp\is_host_in_noproxy;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class ShopWaitingController extends RestLaravelController
{
    use MemberHelper;

    private $service;

    public function __construct(ShopWaitingService $service)
    {
        $this->service = $service;
    }

    public function info(Request $request, $id)
    {
        try {
            $waiting = $this->service->find($id);
            $data = (new ShopWaitingResult())->info($waiting);
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::info', $e->getMessage());
            return $this->failureCode('E0001');
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

            if (is_null($waiting->canWaiting)) {
                throw new \Exception('尚未開放候位');
            }

            //沒有設定候位資訊
            if (is_null($waiting->waitingSetting)) {
                throw new \Exception('無法候位，尚未設定候位資訊');
            }

            $name = $request->input('name');
            $number = $request->input('number');
            $cellphone = $request->input('cellphone');

            //超過可候位人數
            $maxCapacity = $waiting->waitingSetting->capacity;
            if ($number > $maxCapacity) {
                throw new \Exception("僅提供人數{$maxCapacity}位內的候位");
            }

            //取得目前叫號
            $onCallList = $waiting->waitingList->filter(function ($item) {
                return $item->status == WaitingStatus::OnCall;
            });

            $currentNo = 0;
            if (count($onCallList) > 0) {
                $first = $onCallList->first();
                $currentNo = $first->waiting_no;
            }


            $memberID = $this->getMemberId();
            $record = $this->service->create($shopId, $name, $number, $cellphone, $memberID);

            $host = $request->getSchemeAndHttpHost();
            $shopName = $waiting->name;
            $userName = $record->name;
            //傳送簡訊認證
            $this->service->sendWaitingSMS($host, $shopName, $shopId, $userName, $cellphone, $record->id, $record->waiting_no);

            $data = new \stdClass();
            $data->id = $record->id;
            $data->name = $userName;
            $data->cellphone = $record->cellphone;
            $data->number = $record->number;
            $data->waitingNo = $record->waiting_no;
            $data->date = $record->date;
            $data->time = $record->time;
            $data->currentNo = $currentNo;
            $data->status = $record->status;
            return $this->success($data);
        } catch (\Exception $e) {
            Logger::error('ShopWaitingController::create', $e->getMessage());
            return $this->responseFormat($data = null, $code = 'E0001', $message = $e->getMessage());
        }

    }

    public function get(Request $request)
    {

    }

    public function delete(Request $request)
    {

    }

}
