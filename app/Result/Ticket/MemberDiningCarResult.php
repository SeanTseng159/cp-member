<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\DiningCarConfig;

class MemberDiningCarResult extends BaseResult
{
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
    public function list($cars)
    {
        if (!$cars) return [];

        $newCars = [];
        foreach ($cars as $car) {
            $newCar = $this->getCar($car->diningCar);
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
        $result->id = $car->id;
        $result->name = $car->name;
        $result->img = 'https://scontent-iad3-1.cdninstagram.com/vp/e3fb7eaf5c084e3b6e041be70850695f/5C483ACD/t51.2885-15/e35/s480x480/41440210_163360401250877_8689027503124651036_n.jpg';
        $result->category = $this->getCategory($car->category, $car->subCategory);

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
}
