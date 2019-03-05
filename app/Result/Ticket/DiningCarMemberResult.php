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
use App\Traits\DiningCarHelper;

class DiningCarMemberResult extends BaseResult
{
    use DiningCarHelper;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取餐車列表
     * @param $cars
     */
    public function list($memberCars)
    {
        if (!$memberCars) return [];

        $newCars = [];
        foreach ($memberCars as $memberCar) {
            $newCar = $this->getCar($memberCar);
            if ($newCar) $newCars[] = $newCar;
        }

        return $newCars;
    }

    /**
     * 餐車資訊
     * @param $car
     */
    private function getCar($memberCar)
    {
        if (!$memberCar) return null;

        $result = new \stdClass;
        $result->id = $memberCar->diningCar->id;
        $result->name = $memberCar->diningCar->name;
        $result->img = ImageHelper::url($memberCar->diningCar->mainImg);
        $result->categories = $this->getCategories($memberCar->diningCar->category, $memberCar->diningCar->subCategory);
        $result->memberCard = $this->getMemberCard($memberCar);

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
     * 取會員卡資訊
     * @param $memberCar
     */
    public function getMemberCard($memberCar)
    {
        $memberCard = new \stdClass;
        $memberCard->level = $this->getMemberLevel($memberCar->diningCar->memberLevels, $memberCar->amount);
        $memberCard->point = $memberCar->point;
        $memberCard->gift = $memberCar->gift_count;

        return $memberCard;
    }
}
