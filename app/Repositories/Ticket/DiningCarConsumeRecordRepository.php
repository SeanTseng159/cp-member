<?php

namespace App\Repositories\Ticket;

use App\Core\Logger;
use App\Repositories\BaseRepository;
use App\Models\DiningCarConsumeRecord;
use Illuminate\Database\QueryException;
use Exception;

class DiningCarConsumeRecordRepository extends BaseRepository
{
    /**
     * Default model.
     *
     * @var string
     */
    protected $missionModel;

    public function __construct(DiningCarConsumeRecord $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 建立消費記錄
     * @param array $data ['member_id', 'dining_car_id', 'amount']
     * @return mixed
     */
    public function create($data)
    {
        try {
            $model = new DiningCarConsumeRecord;
            $model->member_id = $data['member_id'];
            $model->dining_car_id = $data['dining_car_id'];
            $model->amount = $data['amount'];
            $model->save();

            return $model;
        } catch (QueryException $e) {
            Logger::error('QueryException DiningCarConsumeRecord Error', $e->getMessage());

            return null;
        } catch (Exception $e) {
            Logger::error('Exception DiningCarConsumeRecord Error', $e->getMessage());

            return null;
        }
    }
}
