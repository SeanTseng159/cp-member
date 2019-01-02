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
    private function getCar($car)
    {
        if (!$car) return null;

        $result = new \stdClass;
        $result->name = $car->name;
        $result->img = 'https://scontent-iad3-1.cdninstagram.com/vp/e3fb7eaf5c084e3b6e041be70850695f/5C483ACD/t51.2885-15/e35/s480x480/41440210_163360401250877_8689027503124651036_n.jpg';
        $result->category = $this->getCategory($car->category, $car->subCategory);
        $result->isFavorite = false;
        $result->openStatusCode = $car->open_status;
        $result->openStatus = DiningCarConfig::OPEN_STATUS[$car->open_status];
        $result->distance = $this->calcDistance($this->lat, $this->lng, $car->latitude, $car->longitude, 2, 2) . '公里';

        return $result;
    }

    /**
     * 取分類
     * @param $data
     */
    public function getCategory($category, $subCategory)
    {
        $categoryAry = [];

        if ($category) $categoryAry[] = $category->name;
        if ($subCategory) $categoryAry[] = $subCategory->name;

        return implode('·', $categoryAry);
    }
}
