<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/18
 * Time: 上午 10:03
 */

namespace Ksd\Mediation\Services;

use App\Jobs\CacheReload;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Parameter\Product\AllParameter;
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
     * @param bool $isRefresh
     * @return mixed
     */
    public function products($parameter, $isRefresh = false)
    {
        $categories = $parameter->categories();

        $categoryIds = [];
        if(!empty($categories)) {
            foreach ($categories as $category) {
                $categoryIds = array_merge($categoryIds, $this->filterCategory($category));
            }
        }
        $products = $this->repository->products($categoryIds, $parameter, $isRefresh)->pagination()->sort();
        return $this->processList($products);
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $parameter
     * @param bool $isRefresh
     * @return mixed
     */
    public function product($parameter, $isRefresh = false)
    {
        $product = $this->repository->product($parameter, $isRefresh);
        return $this->process($product, true);
    }

    /**
     * 根據 商品 id 取得加購商品
     * @param $parameter
     * @param bool $isRefresh
     * @return mixed
     */
    public function purchase($parameter, $isRefresh = false)
    {
        $product = $this->repository->purchase($parameter, $isRefresh);
        return $product;
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

            if (is_array($product) && $product['source'] == 'market') {
                $products->result[$key] = $product;
            } else {
                $products->result[$key] = $this->process($product);
            }


        }
        return $products;

    }

    /**
     * 處理商品資料
     * @param $product
     * @param bool $isDetail
     * @return mixed
     */
    public function process($product, $isDetail = false)
    {
        if (!$product) return null;

        $product = $this->wishProduct($product);
        if(isset($product->source) && $product->source === ProjectConfig::MAGENTO) {
            /*$categoryIds = $product->customAttributes($product->customAttributes, 'category_ids', []);
            $categories = [];
            foreach ($categoryIds as $categoryId) {
                $categories[] = $this->filterCategoryById($categoryId);
            }
            $product->tags = $categories;*/
            $product->tags = [];
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
        /*if(isset($this->wishList)) {
            foreach ($this->wishList as $wishRow) {
                if ($product->id == $wishRow['id']) {
                    $product->isWishlist = true;
                    break;
                }
            }
        }*/
        $product->isWishlist = false;
        return $product;
    }

    /**
     * 清除所有商品快取
     * @param int $delayTime
     */
    public function cleanAllProductCache($delayTime = 0)
    {
        $parameter = new AllParameter();
        $parameter->laravelRequest(request());
        /*dispatch(new CacheReload(ProductRepository::CACHE_PRODUCT_ALL,ProductService::class, 'products', [$parameter, true]))
        ->delay($delayTime);*/
    }

    /**
     * 清除單一商品快取
     * @param $parameter
     */
    public function cleanProductCache($parameter)
    {
        /*dispatch(new CacheReload(ProductRepository::CACHE_PRODUCT_ALL,ProductService::class, 'product', [$parameter, true]));
        $this->cleanAllProductCache(30);*/
        dispatch(new CacheReload(ProductRepository::CACHE_PRODUCT_ALL,ProductService::class, 'cleanLayoutProduct', [$parameter]))
            ->delay(60);
    }

    /**
     * 清除 layout 中 magento 商品
     * @param $parameter
     */
    public function cleanLayoutProduct($parameter)
    {
        $this->repository->cleanLayoutProduct($parameter);
    }
}
