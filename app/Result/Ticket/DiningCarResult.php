<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\DiningCarConfig;
use App\Traits\MapHelper;

class DiningCarResult extends BaseResult
{
    use MapHelper;

    private $lat;
    private $lng;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取餐車列表
     * @param $data
     * @param $long
     * @param $lat
     */
    public function list($cars, $lat, $lng)
    {
        if (!$cars) return [];

        $this->lat = $lat;
        $this->lng = $lng;

        $newCars = [];
        foreach ($cars as $car) {
            $newCar = $this->getCar($car);
            if ($newCar) $newCars[] = $newCar;
        }

        return $newCars;
    }

    /**
     * 餐車資訊
     * @param $car
     */
    public function detail($car, $lat, $lng)
    {
        if (!$car) return null;

        $this->lat = $lat;
        $this->lng = $lng;

        $result = new \stdClass;
        $result->id = $car->id;
        $result->name = $car->name;
        $result->description = $car->description;
        $result->imgs = $this->getImgs();
        $result->category = $this->getCategory($car->category, $car->subCategory);
        $result->isFavorite = false;
        $result->openStatusCode = $car->open_status;
        $result->openStatus = DiningCarConfig::OPEN_STATUS[$car->open_status];
        $result->distance = $this->calcDistance($this->lat, $this->lng, $car->latitude, $car->longitude, 2, 2) . '公里';
        $result->businessHoursDays = $this->getBusinessHoursDays($car->businessHoursDays);
        $result->businessHoursDates = $this->getBusinessHoursDates($car->businessHoursDates);
        $result->socialUrls = $this->getSocialUrls($car->socialUrls);

        return $result;
    }

    /**
     * 餐車資訊
     * @param $car
     */
    private function getCar($car)
    {
        if (!$car) return null;

        $result = new \stdClass;
        $result->id = $car->id;
        $result->name = $car->name;
        $result->img = 'https://scontent-iad3-1.cdninstagram.com/vp/e3fb7eaf5c084e3b6e041be70850695f/5C483ACD/t51.2885-15/e35/s480x480/41440210_163360401250877_8689027503124651036_n.jpg';
        $result->category = $this->getCategory($car->category, $car->subCategory);
        $result->isFavorite = false;
        $result->openStatusCode = $car->open_status;
        $result->openStatus = DiningCarConfig::OPEN_STATUS[$car->open_status];
        $result->longitude = $car->longitude;
        $result->latitude = $car->latitude;
        $result->distance = $this->calcDistance($this->lat, $this->lng, $car->latitude, $car->longitude, 2, 2) . '公里';

        return $result;
    }

    /**
     * 取分類
     * @param $data
     */
    private function getCategory($category, $subCategory)
    {
        $categoryAry = [];

        if ($category) $categoryAry[] = $category->name;
        if ($subCategory) $categoryAry[] = $subCategory->name;

        return implode('·', $categoryAry);
    }

    /**
     * 取照片
     * @param $data
     */
    private function getImgs()
    {
        return [
            'https://scontent-iad3-1.cdninstagram.com/vp/e3fb7eaf5c084e3b6e041be70850695f/5C483ACD/t51.2885-15/e35/s480x480/41440210_163360401250877_8689027503124651036_n.jpg',
            'https://scontent-iad3-1.cdninstagram.com/vp/e3fb7eaf5c084e3b6e041be70850695f/5C483ACD/t51.2885-15/e35/s480x480/41440210_163360401250877_8689027503124651036_n.jpg',
            'https://scontent-iad3-1.cdninstagram.com/vp/e3fb7eaf5c084e3b6e041be70850695f/5C483ACD/t51.2885-15/e35/s480x480/41440210_163360401250877_8689027503124651036_n.jpg'
        ];
    }

    /**
     * 取營業時間列表
     * @param $businessHoursDays
     */
    private function getBusinessHoursDays($businessHoursDays)
    {
        if ($businessHoursDays->isEmpty()) return [];

        $newBusinessHoursDays = [];
        foreach ($businessHoursDays as $hoursDay) {
            $newBusinessHoursDays[] = $this->getBusinessHoursDay($hoursDay);
        }

        return $newBusinessHoursDays;
    }

    /**
     * 取營業時間
     * @param $hoursDay
     */
    private function getBusinessHoursDay($hoursDay)
    {
        $result = new \stdClass;
        $result->day = DiningCarConfig::WEEK[$hoursDay->day];
        $result->location = '瑞豐夜市';
        $result->times = $this->getBusinessHoursTimes($hoursDay->times);

        return $result;
    }

    /**
     * 取營業時間
     * @param $times
     */
    private function getBusinessHoursTimes($times)
    {
        if ($times->isEmpty()) return [];

        $newTimes = [];
        foreach ($times as $time) {
            $newTimes[] = $this->getBusinessHoursTime($time);
        }

        return $newTimes;
    }

    /**
     * 取營業時間
     * @param $time
     */
    private function getBusinessHoursTime($time)
    {
        $startTime = substr($time->start_time, 0, 5);
        $endTime = substr($time->end_time, 0, 5);

        return sprintf('%s - %s', $startTime, $endTime);
    }

    /**
     * 取本月營業日
     * @param $businessHoursDays
     */
    private function getBusinessHoursDates($businessHoursDates)
    {
        if ($businessHoursDates->isEmpty()) return [];

        $newBusinessHoursDates = [];
        foreach ($businessHoursDates as $hoursDate) {
            $newBusinessHoursDates[] = $hoursDate->business_date;
        }

        return $newBusinessHoursDates;
    }

    /**
     * 取社群連結
     * @param $socialUrls
     */
    private function getSocialUrls($socialUrls)
    {
        if ($socialUrls->isEmpty()) return [];

        $newSocialUrls = [];
        foreach ($socialUrls as $social) {
            $newSocialUrls[] = $this->getSocialUrl($social);
        }

        return $newSocialUrls;
    }

    /**
     * 取社群連結
     * @param $social
     */
    private function getSocialUrl($social)
    {
        $result = new \stdClass;
        $result->source = $social->source;
        $result->url = $social->url;

        return $result;
    }

    /**
     * 取分類
     * @param $data
     */
    public function getOpenStatusList()
    {
        $list = DiningCarConfig::OPEN_STATUS;

        $newList = [];
        foreach ($list as $key => $value) {
            $status = new \stdClass;
            $status->id = $key;
            $status->name = $value;
            $newList[] = $status;
        }

        return $newList;
    }
}
