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
use App\Helpers\ImageHelper;
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

        $calledRecords = $waiting->waitingList->sortBy(function ($item) {
            return $item->updated_at;
        })->filter(function ($item) {
            return $item->status == WaitingStatus::Called;
        });

        $WaitingRecords = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::Waiting;
        });

        $currentNo = 0;
        if (count($calledRecords) > 0) {
            $first = $calledRecords->last();
            $currentNo = $first->waiting_no;
        }
        $WaitingNum = count($WaitingRecords);
        $result->currentNo = $this->getWaitNoString($currentNo);
        $result->WaitingNum = $WaitingNum;
        $result->capacity = is_null(optional($waiting->waitingSetting)->capacity) ? 0 : $waiting->waitingSetting->capacity;
        return $result;
    }

    public function memberList($data, $memberDiningCars, $totlaPage, $total)
    {
        $ret = new \stdClass();
        $ret->total = $total;
        $ret->page = $totlaPage;

        $list = [];
        foreach ($data as $waitingRecord) {
            $result = new \stdClass;
            //shop
            $shop = $waitingRecord->shop;
            $result->shop = new \stdClass();

            //category
            $category = [];
            $category[] = $shop->category->name;
            $category[] = $shop->subCategory->name;

            $shopId = $shop->id;
            $result->shop->id = $shopId;
            $result->shop->category = $category;
            $result->shop->name = $shop->name;

            $result->shop->isFavorite = $this->isFavorite($memberDiningCars, $shopId);
            $result->shop->shareUrl = $this->getWebHost($shopId);
            $result->shop->photo = ImageHelper::url($shop->mainImg);
            $result->shop->canOnlineOrder = (boolean)$shop->canOrdering;

            //waiting Record
            $result->waitingId = $waitingRecord->id;
            $result->number = $waitingRecord->number;
//            $result->date = DateHelper::chinese($waitingRecord->date, '%Y/%m/%d (%A)');
            $result->date = DateHelper::format($waitingRecord->date, 'Y/m/d');
            $result->time = Carbon::parse($waitingRecord->time)->format('H:i');
            $result->waitingNo = $this->getWaitNoString($waitingRecord->waiting_no);
            $result->code = $waitingRecord->code;
            $list[] = $result;

        }

        $ret->data = $list;
        return $ret;
    }

    public function get($waiting, $memberDiningCars = null)
    {

        $shop = $waiting->shop;

        $data = new \stdClass();

        $data->shop = new \stdClass();
        $data->shop->id = $shop->id;
        $data->shop->name = $shop->name;
        $data->shop->shareUrl = $this->getWebHost($shop->id);
        $data->shop->isFavorite = $this->isFavorite($memberDiningCars, $shop->id);
        $data->shop->img = ImageHelper::url($shop->mainImg);
        $data->shop->canOnlineOrder = (boolean)$shop->canOrdering;

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

    private function isFavorite($memberDiningCars, $shopId)
    {
        if (!$memberDiningCars)
            return false;

        $favoriteList = $memberDiningCars->filter(function ($item) use ($shopId) {
            return $item->dining_car_id == $shopId;
        });


        if ($favoriteList->count() > 0)
            return true;
        return false;
    }


}
