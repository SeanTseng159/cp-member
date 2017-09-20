<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/18
 * Time: 上午 10:03
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Repositories\ProductRepository;

class ProductService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    /**
     * 根據 id 取得所有商品分類
     * @param int $id
     * @return mixed
     */
    public function categories($id = 1)
    {
        return $this->repository->categories($id);
    }

    /**
     * 根據 產品分類 id 陣列 取得所有商品
     * @param $parameter
     * @return $this
     */
    public function products($parameter)
    {
        $categories = $parameter->categories();

        $categoryIds = [];
        if(!empty($categories)) {
            $categoryResult = $this->categories();
            foreach ($categories as $category) {
                $categoryIds = array_merge($categoryIds, $this->filterCategory($categoryResult, $category));
            }
        }
        return $this->repository->products($categoryIds, $parameter)->pagination()->sort();
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $parameter
     * @return mixed
     */
    public function product($parameter)
    {
        return $this->repository->product($parameter);
    }

    /**
     * 根據 關鍵字 搜尋 取得商品列表
     * @param $parameter
     * @return array
     */
    public function search($parameter)
    {
        return $this->repository->search($parameter);
    }

    /**
     * 根據 分類名稱 取得分類 id
     * @param $categoryResult
     * @param $name
     * @return array
     */
    private function filterCategory($categoryResult, $name)
    {
        $ids = [];
        foreach ($categoryResult as $categoryRow) {
            if (!empty($categoryRow)) {
                $row = $categoryRow->filterByName($name);
                if(!empty($row)) {
                    $ids[] = $row->id;
                }
            }
        }
        return $ids;
    }
}