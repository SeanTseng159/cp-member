<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result;

use App\Enum\WaitingStatus;
use App\Helpers\CommonHelper;
use App\Helpers\DateHelper;
use App\Traits\ShopHelper;
use Carbon\Carbon;


class ShopWaitingResult
{
    use shopHelper;


    public function info($waiting)
    {
        $result = new \stdClass;


        //沒有候位清單
        if (!$waiting->waitingList) {
            $result->currentNo = 0;
            $result->WaitingNum = 0;
            $result->capacity = optional($waiting->waitingSetting)->capacity;
            return $result;
        }
        $calledRecords = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::Called;
        });

        $WaitingRecords = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::Waiting;
        });
        $currentNo = count($calledRecords) > 0 ? ($calledRecords->first())->waiting_no : 0;
        $WaitingNum = count($WaitingRecords);
        $result->currentNo = $this->getWaitNoString($currentNo);
        $result->WaitingNum = $WaitingNum;
        $result->capacity = is_null(optional($waiting->waitingSetting)->capacity) ? 0 : $waiting->waitingSetting->capacity;
        return $result;
    }

    public function memberList($data, $memberDiningCars)
    {

        $ret = [];
        foreach ($data as $waitingRecord) {
            $result = new \stdClass;
            //shop
            $shop = $waitingRecord->shop;
            $result->shop = new \stdClass();

            $shopId = $shop->id;
            $result->shop->id = $shopId;
            $result->shop->category = $shop->category->name."/".$shop->subCategory->name;
            $result->shop->name = $shop->name;


            $favoriteList = $memberDiningCars->filter(function ($item) use ($shopId) {
                return $item->dining_car_id = $shopId;
            });
            $result->shop->isFavorite = (count($favoriteList)) > 0 ? true : false;
            $result->shop->shareUrl = $this->getWebHost($shopId);

            //waiting Record
            $result->waitingId = $waitingRecord->id;
            $result->number = $waitingRecord->number;
//            $result->date = DateHelper::chinese($waitingRecord->date, '%Y/%m/%d (%A)');
            $result->date = DateHelper::format($waitingRecord->date, 'Y/m/d');
            $result->time = Carbon::parse($waitingRecord->time)->format('H:i') ;
            $result->waitingNo = $this->getWaitNoString($waitingRecord->waiting_no);
            $result->code = $waitingRecord->code;
            $ret[] = $result;

        }
        return $ret;
    }

    public function get($waiting)
    {

        $shop = $waiting->shop;

        $data = new \stdClass();
        $data->shop = new \stdClass();
        $data->shop->id = $shop->id;
        $data->shop->name = $shop->name;
        $data->shop->shareUrl = $this->getWebHost($shop->id);
        $data->name = $waiting->name;
        $data->cellphone = $waiting->cellphone;
        $data->number = $waiting->number;
        $data->waitingNo = $this->getWaitNoString($waiting->waiting_no);
        $data->code = $waiting->code;
        $data->status = $waiting->status;

        return $data;

    }


    /**
     * @param $shopId
     * @return string
     */
    private function getWebHost($shopId): string
    {
        return CommonHelper::getWebHost('zh-TW/shop/detail/' . $shopId);
    }


}
