<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Config\Ticket\DiningCarConfig;
use App\Helpers\ImageHelper;

class MemberDiningCarResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取餐車列表
     * @param $cars
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
     * 取餐車分類列表
     * @param $data
     * @param $long
     * @param $lat
     */
    public function categories($categories)
    {
        if (!$categories) return [];

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
        $result->description = $car->description;
        $result->img = ImageHelper::url($car->mainImg);
        $result->categories = $this->getCategories($car->category, $car->subCategory);

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
}
