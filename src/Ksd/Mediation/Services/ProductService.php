<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/18
 * Time: 上午 10:03
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Repositories\ProductRepository;

class ProductService
{
    protected $repository;
    private $categoryResult;
    private $wishList;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new ProductRepository();
        $wishListService = new WishlistService($memberTokenService);
        $this->wishList = $wishListService->items();
        $this->categoryResult = $this->categories();
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
            foreach ($categories as $category) {
                $categoryIds = array_merge($categoryIds, $this->filterCategory($category));
            }
        }
        $products = $this->repository->products($categoryIds, $parameter)->pagination()->sort();
        return $this->processList($products);
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $parameter
     * @return mixed
     */
    public function product($parameter)
    {
        $product = $this->repository->product($parameter);
        return $this->process($product, true);
    }

    /**
     * 根據 關鍵字 搜尋 取得商品列表
     * @param $parameter
     * @return array
     */
    public function search($parameter)
    {
        $products = $this->repository->search($parameter);
        return $this->processList($products)->result;
    }

    /**
     * 根據 分類名稱 取得分類 id
     * @param $name
     * @return array
     */
    private function filterCategory($name)
    {
        $ids = [];
        foreach ($this->categoryResult  as $categoryRow) {
            if (!empty($categoryRow)) {
                $row = $categoryRow->filterByName($name);
                if(!empty($row)) {
                    $ids[] = $row->id;
                }
            }
        }
        return $ids;
    }

    /**
     * 根據 id 取得分類
     * @param $id
     * @return array|null
     */
    private function filterCategoryById($id)
    {
        $category = null;
        foreach ($this->categoryResult  as $categoryRow) {
            if (!empty($categoryRow)) {
                $row = $categoryRow->filterById($id);
                if(!empty($row)) {
                    $category = [
                        'id' => $row->id,
                        'name' => $row->name
                    ];
                }
            }
        }
        return $category;
    }

    /**
     * 處理商品列表資料
     * @param $products
     * @return mixed
     */
    private function processList($products)
    {
        foreach ($products->result as $key => $product) {
            $products->result[$key] = $this->process($product);
        }
        return $products;

    }

    /**
     * 處理商品資料
     * @param $product
     * @param bool $isDetail
     * @return mixed
     */
    private function process($product, $isDetail = false)
    {
        $product = $this->wishProduct($product);
        if($product->source === ProjectConfig::MAGENTO) {
            $categoryIds = $product->customAttributes($product->customAttributes, 'category_ids', []);
            $categories = [];
            foreach ($categoryIds as $categoryId) {
                $categories[] = $this->filterCategoryById($categoryId);
            }
            $product->tags = $categories;
        }
        return $product->apiFormat($isDetail);
    }

    /**
     * 增加產品收藏判斷
     * @param $product
     * @return mixed
     */
    private function wishProduct($product)
    {
        if(isset($this->wishList)) {
            foreach ($this->wishList as $wishRow) {
                if (isset($wishRow->source) && $wishRow->source == 'magento') {
                    if ($product->id == $wishRow->id) {
                        $product->isWishlist = true;
                        break;
                    }
                }
            }
        }
        return $product;
    }
}