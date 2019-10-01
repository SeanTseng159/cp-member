<?php
/**
 * User: annie
 * Date: 2019/09/19
 */

namespace App\Result\Ticket;

use App\Config\Ticket\DiningCarConfig;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
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

    /**
     * 店鋪資訊
     * @param $car
     */
    public function detail($car, $isFavorite = false, $lat, $lng)
    {

        if (!$car) return null;

        $this->lat = $lat;
        $this->lng = $lng;

        $result = new \stdClass;
        $result->id = $car->id;
        $result->hashId = $this->encryptHashId('DiningCar', $car->id);
        $result->name = $car->name;
        $result->description = $car->description;
        $result->img = $this->getImg($car->mainImg);
        $result->imgs = $this->getImgs($car->imgs);
        $result->categories = $this->getCategories($car->category, $car->subCategory);

        $result->isFoodCategory=$car->category->isfood;

        $result->isFavorite = $this->getFavorite($car->id);
        $result->openStatusCode = $car->open_status;
        $result->openStatus = DiningCarConfig::OPEN_STATUS[$car->open_status];

        $result->shop=$this->getShopInfo($car);

        $result->longitude = $car->longitude ?? '';
        $result->latitude = $car->latitude ?? '';
        $result->distance = ($result->longitude && $result->latitude && $this->lat && $this->lng) ? $this->calcDistance($this->lat, $this->lng, $car->latitude, $car->longitude, 2, 2) . '公里' : '未知';

        $result->businessHoursDays = $this->getBusinessHoursDays($car->businessHoursDays);
        $result->businessHoursDates = $this->getBusinessHoursDates($car->businessHoursDates);
        $result->level = $this->getLevel($car->level, $car->expired_at); // 是否付費
        $result->socialUrls = $this->getSocialUrls($car->socialUrls, $result->level);

        $result->shareUrl = CommonHelper::getWebHost('zh-TW/shop/detail/' . $car->id);
        $result->videos = $this->getVideos($car->media);
        $result->memberCard = $this->getMemberCard($car->memberCard, $car->memberLevels);
        $result->acls = $this->getAcls($car, $result->level, $result->memberCard);




        return $result;
    }

    //取出shop相關狀態
    public function getShopInfo($car)
    {
        //整理成array
        $shop = new \stdClass;
        $shop->canBooking = (bool)$car->canBooking;
        $shop->canWaiting = (bool)$car->canWaiting;
        $shop->canPointing = ($car->level == 1 && (Carbon::parse($car->expired_at)->gt(Carbon::now())))
             ? true : false;
        return $shop;
    }


    public function servicelist()
    {
        $result = [];
        $dataname=['會員集點','線上訂位','現場候位'];

        foreach ($dataname as $id => $value)
        {
          $data = new \stdClass();
          $data->id = $id;
          $data->name = $value;
          $result[] = $data;
        }

        return $result;
    }
}
