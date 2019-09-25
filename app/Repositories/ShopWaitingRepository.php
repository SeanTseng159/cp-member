<?php


namespace App\Repositories;


use App\Enum\WaitingStatus;
use App\Models\ShopWaiting;
use App\Models\ShopWaitingRecord;
use App\Models\Ticket\DiningCar;
use Carbon\Carbon;


class ShopWaitingRepository extends BaseRepository
{
    private $limit = 20;
    protected $model;
    protected $diningCarModel;
    protected $waitingRecord;


    public function __construct(ShopWaiting $model, DiningCar $diningCarModel, ShopWaitingRecord $waitingRecord)
    {
        $this->model = $model;
        $this->diningCarModel = $diningCarModel;
        $this->waitingRecord = $waitingRecord;
    }

    public function find($diningCarId)
    {
        return $this->diningCarModel->with(['waitingSetting', 'waitingList'])
            ->where('id', $diningCarId)
            ->first();
    }

    public function create($id, $name, $number, $cellphone, $memberId = null)
    {
        $maxNo = $this->waitingRecord->where('dining_car_id', $id)->max('waiting_no');

        return $this->waitingRecord->create([
            'dining_car_id' => $id,
            'waiting_no' => ++$maxNo,
            'member_id' => $memberId,
            'date' => (Carbon::now())->format('Y-m-d'),
            'time' => (Carbon::now())->format('H:i:s'),
            'name' => $name,
            'cellphone' => $cellphone,
            'number' => $number,
            'status' => WaitingStatus::Waiting
        ]);

    }

}
