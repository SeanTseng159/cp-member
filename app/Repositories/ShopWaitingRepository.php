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

    public function getByCode($code)
    {

        return $this->waitingRecord->with('shop')
            ->where('code', $code)
            ->first();
    }

    public function create($id, $name, $number, $cellphone, $memberId = null)
    {
        return \DB::connection('backend')->transaction(function () use (
            $id, $name, $number, $cellphone, $memberId, &$record
        ) {
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
                'status' => WaitingStatus::Waiting,
            ]);
            $waitingId = $record->id;
            $code = $this->getWaitingCode($waitingId);
            $record->code = $code;

            $this->waitingRecord
                ->where('id', $waitingId)
                ->update([
                    'code' => $code
                ]);
            return $record;
        });

    }

    public function get($shopId, $waitingNo)
    {
        return $this->waitingRecord->with('shop')
            ->where('dining_car_id', $shopId)
            ->where('waiting_no', $waitingNo)
            ->first();
    }

    public function delete($shopId, $waitingId, $memberId)
    {
        return $this->waitingRecord
            ->where('dining_car_id', $shopId)
            ->where('id', $waitingId)
            ->where('member_id', $memberId)
            ->delete();
    }

    public function deleteByCode($code)
    {
        return $this->waitingRecord
            ->where('code', $code)
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

    public function getMemberList($memberId, $page = 1)
    {
        return $this->waitingRecord->with('shop', 'shop.category', 'shop.subCategory', 'shop.mainImg')
            ->where('member_id', $memberId)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->orderBy('date', 'desc')
            ->orderBy('waiting_no', 'desc')
            ->forPage($page, $this->limit)
            ->get();
    }

    public function getMemberListPageCount($memberId)
    {
        $total = $this->waitingRecord
            ->where('member_id', $memberId)
            ->where('date', '>=', Carbon::now()->subDays(30))
            ->count();
        $totalPage = ceil($total / $this->limit);
        return [$total, $totalPage];
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
