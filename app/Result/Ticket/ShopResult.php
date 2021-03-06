<?php
/**
 * User: annie
 * Date: 2019/09/19
 */

namespace App\Result\Ticket;

use App\Config\Ticket\DiningCarConfig;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use App\Services\Ticket\DiningCarService;
use App;

class ShopResult extends DiningCarResult
{
    protected $lat;
    protected $lng;
    protected $dayOfWeek;
    protected $memberDiningCars;

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

        //添加是否線上點菜
        $result->canOnlineOrder = ($car->canOrdering) ? true : false;

        //添加是否線上付款
        $result->canEC = $car->employee ? (($car->employee->first()->supplier->canEC==1)?true:false) : false;


        return $result;
    }

    /**
     * 店鋪資訊
     * @param $car
     */
    public function detail($car, $isFavorite = false, $lat, $lng)
    {

        if (!$car) return null;

        $address = $car->county . $car->district . $car->address;

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

        $result->isFoodCategory = $car->category->isfood;

        $result->isFavorite = $isFavorite;
        $result->openStatusCode = $car->open_status;
        $result->openStatus = DiningCarConfig::OPEN_STATUS[$car->open_status];

        $result->shop = $this->getShopInfo($car);

        $result->longitude = $car->longitude ?? '';
        $result->latitude = $car->latitude ?? '';
        $result->phone = $car->phone ?? '';
        $result->address = $address;
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


        if ($car->canQuestionnaire) {
            if (empty($car->currentQuestion)) {
                $shop->canQuestionnaire = false;
            } elseif ($car->currentQuestion->status) {
                $shop->canQuestionnaire = true;
            } else {
                $shop->canQuestionnaire = false;
            }
        } else {
            $shop->canQuestionnaire = false;
        }

        $shop->canPointing = ($car->level == 1 && (Carbon::parse($car->expired_at)->gt(Carbon::now())))
            ? true : false;

        //添加是否線上點菜
        $shop->canOnlineOrder = ($car->canOrdering) ? true : false;
        //添加是否線上付款
        $shop->canEC = $car->employee ? (bool)$car->employee->first()->supplier->canEC : false;

        return $shop;
    }


    public function servicelist()
    {
        $result = [];
        $dataname = ['會員集點', '線上訂位', '現場候位', '線上點餐'];

        foreach ($dataname as $id => $value) {
            $data = new \stdClass();
            $data->id = $id;
            $data->name = $value;
            $result[] = $data;
        }

        return $result;
    }

    public function list($cars, $lat, $lng, $memberDiningCars = null)
    {
        if (!$cars) return [];

        $this->lat = $lat;
        $this->lng = $lng;
        $this->memberDiningCars = $memberDiningCars;
        $newCars = [];
        foreach ($cars as $key => $car) {
            $newCar = $this->getCar($car);
            if ($newCar) $newCars[] = $newCar;
        }


        return $newCars;
    }//end list
}
