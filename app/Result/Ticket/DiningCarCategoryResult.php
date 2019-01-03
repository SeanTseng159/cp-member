<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;

class DiningCarCategoryResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取餐車分類列表
     * @param $data
     * @param $long
     * @param $lat
     */
    public function main($categories = [])
    {
        if (!$categories) return [];

        $newCategories = [];
        foreach ($categories as $category) {
            $newCategory = $this->getCategory($category);
            if ($newCategory) $newCategories[] = $newCategory;
        }

        return $newCategories;
    }

    /**
     * 分類資訊
     * @param $car
     */
    private function getCategory($category)
    {
        if (!$category) return null;

        $result = new \stdClass;
        $result->id = $category->id;
        $result->name = $category->name;

        return $result;
    }
}
