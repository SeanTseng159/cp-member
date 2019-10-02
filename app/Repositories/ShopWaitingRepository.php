<?php


namespace App\Repositories;


use App\Enum\WaitingStatus;
use App\Models\ShopWaiting;
use App\Models\ShopWaitingRecord;
use App\Models\Ticket\DiningCar;
use Carbon\Carbon;
use Hashids\Hashids;


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
        return $this->diningCarModel->with(['waitingSetting', 'waitingList', 'businessHoursDays', 'businessHoursDays.times'])
            ->where('id', $diningCarId)
            ->first();
    }

    public function create($id, $name, $number, $cellphone, $memberId = null)
    {
        $maxNo = $this->waitingRecord
            ->where('dining_car_id', $id)
            ->where('date', Carbon::now()->format('Y-m-d'))
            ->max('waiting_no');


        $record = $this->waitingRecord->create([
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
        $waitingId = $record->id;
        $code = $this->getWaitingCode($waitingId);

        $this->waitingRecord
            ->where('id', $waitingId)
            ->update([
                'code' => $code
            ]);

        return $record;

    }

    public function get($shopId, $waitingId)
    {
        return $this->waitingRecord->with('shop')
            ->where('dining_car_id', $shopId)
            ->where('id', $waitingId)
            ->first();
    }

    public function delete($shopId, $waitingId)
    {
        return $this->waitingRecord
            ->where('dining_car_id', $shopId)
            ->where('id', $waitingId)
            ->delete();
    }

    public function getWaitingNumber($shopId, $waitingNo)
    {
        return $this->waitingRecord
            ->where('waiting_no', '<', $waitingNo)
            ->where('status', 0)
            ->where('dining_car_id', $shopId)
            ->where('date', Carbon::now()->format('Y-m-d'))
            ->count();
    }

    public function getMemberList($memberId)
    {
        return $this->waitingRecord->with('shop')
            ->where('member_id', $memberId)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

    }

    public function decode($code)
    {
        return $this->waitingRecord
            ->where('code', $code)
            ->first();

    }

    private function getWaitingCode($waitingId)
    {
        $hashids = new Hashids('citypass', 7);
        return $hashids->encode($waitingId);
    }

}
