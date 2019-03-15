<?php

namespace App\Repositories\Ticket;

use DB;
use App\Core\Logger;
use Illuminate\Database\QueryException;

use App\Repositories\BaseRepository;
use App\Models\Ticket\DiningCarPointRecord;
use App\Models\Ticket\DiningCarConsumeRecord;

class DiningCarPointRecordRepository extends BaseRepository
{
    /**
     * Default model.
     *
     * @var string
     */
    protected $model;

    public function __construct(DiningCarPointRecord $model)
    {
        $this->model = $model;
    }

    /**
     * 儲存兌換點數及消費記錄
     * @param int $diningCarId
     * @param int $memberId
     * @param int $consumeAmount
     * @param $rule
     * @return int [換得點數]
     */
    public function saveExchangePoint($diningCarId = 0, $memberId = 0, $consumeAmount = 0, $rule)
    {
        try {
            DB::connection('backend')->beginTransaction();

            // 換得點數
            $point = floor($consumeAmount / $rule->point);

            // 寫入點數
            if ($point > 0) {
                $pointRecord = new DiningCarPointRecord;
                $pointRecord->member_id = $memberId;
                $pointRecord->dining_car_id = $diningCarId;
                $pointRecord->point = $point;
                $pointRecord->status = 1;
                $pointRecord->expired_at = $rule->expired_at;
                $pointRecord->model_spec_id = $rule->id;
                $pointRecord->model_type = 'dining_car_point_rule';
                $pointRecord->model_name = 'DiningCarPointRule';
                $pointRecord->save();

                $consumeRecordData['dining_car_point_record_id'] = $pointRecord->id;
            }

            // 寫入消費記錄
            $consumeRecordData['member_id'] = $memberId;
            $consumeRecordData['dining_car_id'] = $diningCarId;
            $consumeRecordData['amount'] = $consumeAmount;
            DiningCarConsumeRecord::insert($consumeRecordData);

            DB::connection('backend')->commit();

            return $point;
        } catch (QueryException $e) {
            Logger::error('QueryException saveExchangePoint Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            return 0;
        } catch (Exception $e) {
            Logger::error('Exception saveExchangePoint Error', $e->getMessage());
            DB::connection('backend')->rollBack();

            return 0;
        }
    }

    /**
     * 取總點數
     * @param int $memberId
     * @param int $diningCarId
     * @return int
     */
    public function getTotalPointByDiningCarId($memberId = 0, $diningCarId = 0)
    {
        return $this->model->where('member_id', $memberId)
                            ->where('dining_car_id', $diningCarId)
                            ->isEffective()
                            ->sum('point');
    }
}
