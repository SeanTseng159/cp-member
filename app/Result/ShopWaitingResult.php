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

        $oncallRecords = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::Called;
        });
        dd($oncallRecords);

        $WaitingRecords = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::Waiting;
        });
        $currentNo = count($oncallRecords) > 0 ? ($oncallRecords->first())->waiting_no : 0;
        $WaitingNum = count($WaitingRecords);
        $result->currentNo = $this->getWaitNoString($currentNo);
        $result->WaitingNum = $WaitingNum;
        $result->capacity = $waiting->waitingSetting->capacity;
        return $result;
    }

    public function memberList($data, $memberDiningCars)
    {

        $ret = [];
        foreach ($data as $waitingRecord) {
            $result = new \stdClass;
            //shop
            $shop = $waitingRecord->shop->first();
            $result->shop = new \stdClass();

            $shopId = $shop->id;
            $result->shop->id = $shopId;
            $result->shop->name = $shop->name;

            $favoriteList = $memberDiningCars->filter(function ($item) use ($shopId) {
                return $item->dining_car_id = $shopId;
            });
            $result->shop->isFavorite = (count($favoriteList)) > 0 ? true : false;
            $result->shop->shareUrl = $this->getWebHost($shopId);

            //waiting Record
            $result->waitingId = $waitingRecord->id;
            $result->number = $waitingRecord->number;
            $result->date = DateHelper::chinese($waitingRecord->date, '%Y/%m/%d (%A)');
            $result->waitingNo = $this->getWaitNoString($waitingRecord->waiting_no);
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
        $data->cellphone = $waiting->cellphone;
        $data->number = $waiting->number;
        $data->date = $waiting->date;
        $data->time = $waiting->time;
        $data->waitingNo = $this->getWaitNoString($waiting->waiting_no);
//        $data->currentNo = $currentNo;
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
