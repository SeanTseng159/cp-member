<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Helpers\ImageHelper;
use App\Traits\DiningCarHelper;

class MemberDiningCarResult extends BaseResult
{
    use DiningCarHelper;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取餐車列表
     * @param $cars
     * @return array
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
     * @param $categories
     * @return array
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
     * @return \stdClass|null
     */
    private function getCar($car)
    {
        if (!$car) return null;

        $result = new \stdClass;
        $result->id = $car->id;
        $result->name = $car->name;
        $result->description = $car->description;
        $result->img = ImageHelper::url($car->mainImg, 's');
        $result->categories = $this->getCategories($car->category, $car->subCategory);
        $result->memberCard = $this->getMemberCard($car->memberCard, $car->memberLevels);
        $result->type=$car->type;

        return $result;
    }

    /**
     * 取分類
     * @param $category
     * @param $subCategory
     * @return array
     */
    private function getCategories($category, $subCategory)
    {
        $categoryAry = [];

        if ($category) $categoryAry[] = $category->name;
        if ($subCategory) $categoryAry[] = $subCategory->name;

        return $categoryAry;
    }

    /**
     * 取會員卡資訊
     * @param $memberCard
     * @param $memberLevels
     * @return \stdClass
     */
    private function getMemberCard($memberCard, $memberLevels)
    {
        $result = new \stdClass;

        if (!$memberCard) {
            // 還未加入會員
            $result->level = -1;
        }
        else {
            // 已加入會員
            $result->level = $this->getMemberLevel($memberLevels, $memberCard->amount);
        }

        return $result;
    }
}
