<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;


use App\Core\Logger;
use App\Enum\DiningCarPointRecordType;
use App\Models\Gift;
use App\Models\MemberGiftItem;
use App\Models\Ticket\DiningCarPointRecord;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use libphonenumber\Leniency\StrictGrouping;
use phpDocumentor\Reflection\Types\Array_;


class DiningCarPointRecordRepository extends BaseRepository
{
    protected $diningCarPointRecord;
    protected $memberGiftItem;

    public function __construct(DiningCarPointRecord $diningCarPointRecord, MemberGiftItem $memberGiftItem)
    {
        $this->diningCarPointRecord = $diningCarPointRecord;
        $this->memberGiftItem = $memberGiftItem;
    }

    public function total($memberId, $diningCarId)
    {
        return intval($this->diningCarPointRecord
            ->allow()
            ->where('member_id', $memberId)
            ->where('dining_car_id', $diningCarId)
            ->sum('point'));


    }

    public function create($memberId, $diningCarId, $point, $expired_at, $giftId, $qty)
    {

        try {
            DB::connection('backend')->beginTransaction();

            //點數兌換紀錄
            $record = new $this->diningCarPointRecord;
            $record->member_id = $memberId;
            $record->dining_car_id = $diningCarId;
            $record->point = $point * (-1);
            $record->status = 1;
            $record->expired_at = $expired_at;
            $record->model_spec_id = $giftId;
            $record->model_type = DiningCarPointRecordType::gift;
            $record->model_name = Gift::class;
            $record->save();

            //禮物兌換紀錄
            $number = $this->memberGiftItem->where('member_id', $memberId)->where('gift_id', $giftId)->max('number');

            $objList = [];
            for ($i = 1; $i <= $qty; $i++) {
                $objList[] = [
                    'member_id' => $memberId,
                    'gift_id' => $giftId,
                    'number' => $number + $i,
                    'used_time' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'created_at' => Carbon::now()
                ];
            }
            $ret = $this->memberGiftItem->insert($objList);
            DB::connection('backend')->commit();

            return true;


        } catch (\Exception $e) {
            Logger::error('QueryException Create Exchange Gift Error', $e->getMessage());
            DB::connection('backend')->rollBack();
            dd($e->getMessage());
            return false;


        }


    }


}
