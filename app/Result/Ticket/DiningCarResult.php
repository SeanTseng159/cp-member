<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use Carbon\Carbon;
use App\Config\Ticket\DiningCarConfig;
use App\Traits\CryptHelper;
use App\Traits\MapHelper;
use App\Traits\DiningCarHelper;
use App\Helpers\CommonHelper;
use App\Helpers\ImageHelper;

class DiningCarResult extends BaseResult
{
    use MapHelper, CryptHelper, DiningCarHelper;

    private $lat;
    private $lng;
    private $dayOfWeek;
    private $memberDiningCars;

    public function __construct()
    {
        parent::__construct();

        $this->dayOfWeek = Carbon::today()->dayOfWeek;
        if ($this->dayOfWeek === 0) $this->dayOfWeek = 7;
    }

    /**
     * 取餐車列表
     *
     * @param $cars
     * @param $memberDiningCars
     * @param $lat
     *
     * @param $lng
     *
     * @return array
     */
    public function list($cars, $memberDiningCars = null, $lat, $lng)
    {
        if (!$cars) return [];

        $this->lat = $lat;
        $this->lng = $lng;
        $this->memberDiningCars = $memberDiningCars;

        $newCars = [];
        foreach ($cars as $car) {
            $newCar = $this->getCar($car);
            if ($newCar) $newCars[] = $newCar;
        }

        return $newCars;
    }

    /**
     * 餐車資訊
     *
     * @param $car
     *
     * @return \stdClass|null
     */
    private function getCar($car)
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

        // 計算距離
        $result->longitude = $car->longitude ?? '';
        $result->latitude = $car->latitude ?? '';
        $result->distance = ($result->longitude && $result->latitude && $this->lat && $this->lng) ? $this->calcDistance($this->lat, $this->lng, $car->latitude, $car->longitude, 2, 2) . '公里' : '未知';

        return $result;
    }

    /**
     * 餐車資訊
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
        $result->isFavorite = $isFavorite;
        $result->openStatusCode = $car->open_status;
        $result->openStatus = DiningCarConfig::OPEN_STATUS[$car->open_status];
        $result->longitude = $car->longitude ?? '';
        $result->latitude = $car->latitude ?? '';
        $result->distance = ($result->longitude && $result->latitude && $this->lat && $this->lng) ? $this->calcDistance($this->lat, $this->lng, $car->latitude, $car->longitude, 2, 2) . '公里' : '未知';
        $result->businessHoursDays = $this->getBusinessHoursDays($car->businessHoursDays);
        $result->businessHoursDates = $this->getBusinessHoursDates($car->businessHoursDates);
        $result->socialUrls = $this->getSocialUrls($car->socialUrls);
        $result->shareUrl = CommonHelper::getWebHost('zh-TW/diningCar/detail/' . $car->id);
        $result->videos = $this->getVideos($car->media);
        $result->level = $this->getLevel($car->level, $car->expired_at);
        $result->memberCard = $this->getMemberCard($car->memberCard, $car->memberLevels, $car->giftCount, $car->totalCount);

        return $result;
    }

    /**
     * 取分類
     * @param $data
     */
    private function getCategories($category, $subCategory)
    {
        $categoryAry = [];

        if ($category) $categoryAry[] = $category->name;
        if ($subCategory) $categoryAry[] = $subCategory->name;

        return $categoryAry;
    }

    /**
     * 取收藏
     * @param $id
     */
    public function getFavorite($id)
    {
        if (!$this->memberDiningCars) return false;
        return ($this->memberDiningCars->where('dining_car_id', $id)->first()) ? true : false;
    }

    /**
     * 取封面照
     * @param $data
     */
    private function getImg($img)
    {
        return ImageHelper::url($img);
    }

    /**
     * 取照片
     * @param $data
     */
    private function getImgs($imgs)
    {
        return ImageHelper::urls($imgs);
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
        $result->location = $hoursDay->location;
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
     * 取營業狀態
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

    /**
     * 取影片
     * @param $media
     */
    private function getVideos($media)
    {
        $videos = [];

        $video = $this->getVideo($media);
        if ($video) $videos[] = $video;

        return $videos;
    }

    /**
     * 取影片
     * @param $media
     */
    private function getVideo($media)
    {
        if (!$media) return null;

        parse_str(parse_url($media->link, PHP_URL_QUERY), $query);
        if (!$query || !isset($query['v']) || !$query['v']) return null;

        return $query['v'];
    }

    /**
     * 取餐車等級
     * @param $data
     */
    public function getLevel($level, $expired_at)
    {
        if ($level === 0) return $level;
        if (!$expired_at) return 0;

        $now = Carbon::now();
        $expired = Carbon::parse($expired_at);

        return ($now->lt($expired)) ? $level : 0;
    }

    /**
     * 取會員卡資訊
     * @param $data
     */
    public function getMemberCard($memberCard, $memberLevels, $giftCount = 0, $totalPoint = 0)
    {
        $result = new \stdClass;

        if (!$memberCard) {
            // 還未加入會員
            $result->level = -1;
            $result->point = 0;
            $result->gift = 0;
        }
        else {
            // 已加入會員
            $result->level = $this->getMemberLevel($memberLevels, $memberCard->amount);
            $result->point = (int) $totalPoint;
            $result->gift = $giftCount;
        }

        return $result;
    }
}
