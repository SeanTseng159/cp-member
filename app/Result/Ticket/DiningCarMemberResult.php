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
    public function getMemberCard($memberCard)
    {
        $result = new \stdClass;
        $result->level = $this->getMemberLevel($memberCard->diningCar->memberLevels, $memberCard->amount);
        $result->point = $memberCard->point;
        $result->gift = $this->calcGiftCount($memberCard->gifts);

        return $result;
    }

    /**
     * 計算禮物數
     * @param $gifts
     * @return int
     */
    private function calcGiftCount($gifts) : int
    {
        if (!$gifts || $gifts->isEmpty()) return 0;

        $count = 0;
        foreach ($gifts as $gift) {
            $count += $gift->memberGiftItems->count();
        }

        return $count;
    }
}
