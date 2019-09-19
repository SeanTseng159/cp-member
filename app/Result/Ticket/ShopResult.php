<?php
/**
 * User: annie
 * Date: 2019/09/19
 */

namespace App\Result\Ticket;

use App\Config\Ticket\DiningCarConfig;
use Carbon\Carbon;

class ShopResult extends DiningCarResult
{

    protected function getCar($car)
    {
        if (!$car) return null;

        $result = new \stdClass;
        $result->id = $car->id;
        $result->name = $car->name;
        $result->description = $car->description;
        $result->img = $this->getImg($car->mainImg);
        $result->categories = $this->getCategories($car->category, $car->subCategory);
        $result->isFavorite = $this->getFavorite($car->id);
        $result->openStatusCode = $car->open_status;
        $result->openStatus = DiningCarConfig::OPEN_STATUS[$car->open_status];

        $result->canBooking = (bool)$car->canBooking;
        $result->canWaiting = (bool)$car->canWaiting;
        //是付費店家
        $result->canPointing = ($car->level == 1 && (Carbon::parse($car->expired_at)->gt(Carbon::now())))
            ? true : false;


        // 計算距離
        $result->longitude = $car->longitude ?? '';
        $result->latitude = $car->latitude ?? '';
        $result->distance = ($result->longitude && $result->latitude && $this->lat && $this->lng) ?
            $this->calcDistance($this->lat, $this->lng, $car->latitude, $car->longitude, 2, 2) . '公里'
            :
            '未知';

        return $result;
    }


}
