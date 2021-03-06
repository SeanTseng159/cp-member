<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 下午 2:42
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Cache\Redis;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Config\CacheConfig;
use Ksd\Mediation\Magento\Product as MagentoProduct;
use Ksd\Mediation\CityPass\Product as CityPassProduct;
use Ksd\Mediation\Parameter\Product\QueryParameter;
use Ksd\Mediation\Result\Collection;
use Ksd\Mediation\Result\Product\ProductIndexResult;

class ProductRepository extends BaseRepository
{
    const CACHE_INDEX = '%s:product:index';
    const CACHE_PRODUCT_ALL = 'product:category:all';

    public function __construct()
    {
        $this->redis = new Redis();
        $this->magento = new MagentoProduct();
        $this->cityPass = new CityPassProduct();
    }

    /**
     * 根據 id 取得所有商品分類
     * @param int $id
     * @return mixed
     */
    public function categories($id = 1)
    {
        $result = $this->redis->remember("categories", CacheConfig::TEST_TIME, function () use ($id) {
            $magentoProducts = $this->magento->categories($id);
            $tpassProducts = [];//$this->tpass->products($category);
            return [$magentoProducts, $tpassProducts];
        });
        return $result;
    }

    /**
     * 根據 產品分類 id 陣列 取得所有商品
     * @param null $categories
     * @param null $parameter
     * @return Collection
     */
    public function products($categories = null, $parameter = null, $isRefresh = false)
    {
        $result = [];
        if (empty($categories) && empty($parameter->categories())) {
            $result = $this->redis->remember(self::CACHE_PRODUCT_ALL, CacheConfig::TEST_TIME, function () {
                $magentoProducts = $this->magento->all();
                $magentoProducts = $this->magentoProducts($magentoProducts);
                $cityPassProducts = $this->cityPass->all();
                return array_merge($magentoProducts, $cityPassProducts);
            }, $isRefresh);
        } else {
            $source = ProjectConfig::MAGENTO;
            foreach ($categories as $category) {
                $row = $this->redis->remember("$source:product:category:$category", CacheConfig::TEST_TIME, function () use ($category) {
                    return $this->magento->all($category);
                }, $isRefresh);
                $result = array_merge($result,$row);
            }
            $result = $this->magentoProducts($result);

            $source = ProjectConfig::CITY_PASS;
            $categoryKey = join('_', $parameter->categories());
            $cityPassProducts = $this->redis->remember("$source:product:category:$categoryKey", CacheConfig::TEST_TIME, function () use ($parameter) {
                return $this->cityPass->tags($parameter->categories());
            }, $isRefresh);
            $result = array_merge($result, $cityPassProducts);
        }

        return new Collection($result, $parameter);
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $parameter
     * @return mixed
     */
    public function product($parameter, $isRefresh = false)
    {
        $id = $parameter->no;
        $source = $parameter->source;
        $product = $this->redis->remember("$source:product:id:$id", CacheConfig::TEST_TIME, function () use ($source,$id) {
            $product = null;
            if($source == ProjectConfig::MAGENTO) {
                $product = $this->magento->find($id);
            } else {
                $product = $this->cityPass->find($id);
            }
            return $product;
        }, $isRefresh);
        $this->createIndex($source, $product);
        return $product;
    }

    /**
     * 根據 商品 id 取得加購商品
     * @param $parameter
     * @return mixed
     */
    public function purchase($parameter, $isRefresh = false)
    {
        $id = $parameter->id;
        $source = $parameter->source;
        $purchase = $this->redis->remember("$source:purchase:id:$id", CacheConfig::TEST_TIME, function () use ($source, $id) {
            $purchase = null;
            if ($source == ProjectConfig::MAGENTO) {
                // $product = $this->magento->purchase($id);
            } else {
                $purchase = $this->cityPass->purchase($id);
            }
            return $purchase;
        }, $isRefresh);

        return $purchase;
    }

    /**
     * 根據 關鍵字 搜尋 取得商品列表
     * @param $parameter
     * @return array
     */
    public function search($parameter)
    {

//        $magento = $this->magento->search($parameter);
        $cityPass = $this->cityPass->search($parameter);

//        if (!$magento) $magento = [];
        $magento = [];
        if (!$cityPass) $cityPass = [];

        $data = array_filter(array_merge($magento, $cityPass));

        return new Collection($data, $parameter) ?: null;
    }

    /**
     * 建立索引檔
     * @param $source
     * @param $product
     */
    public function createIndex($source, $product)
    {
        if (empty($product)) {
            return ;
        }
        $cacheKey = $this->getCacheKey(self::CACHE_INDEX, [$source]);
        $indexResults = $this->redis->get($cacheKey);
        if (empty($indexResults)) {
            $indexResults = [];
        }

        $productIndex = new ProductIndexResult();
        if($source == ProjectConfig::MAGENTO) {
            $productIndex->magneto($product);
        }

        $hasIndex = false;

        if (!empty($indexResults) && isset($productIndex->id)) {
            foreach ($indexResults as $row) {
                if (!empty($row->find($productIndex->id))) {
                    $hasIndex = true;
                    break;
                }
            }
            if (!$hasIndex) {
                $indexResults = array_merge($indexResults, [$productIndex]);
            }
        }

        $this->redis->set($cacheKey, $indexResults, 3600 * 24);

    }

    /**
     * 根據 來源 id 查詢商品索引檔
     * @param $source
     * @param $id
     * @return null
     */
    public function findFromIndex($source, $id)
    {
        $cacheKey = $this->getCacheKey(self::CACHE_INDEX, [$source]);
        $indexResults = $this->redis->get($cacheKey);
        if($indexResults) {
            foreach ($indexResults as $row) {
                $index = $row->find($id);
                if (!empty($index)) {
                    return $index;
                }
            }
        }
        return null;
    }

    /**
     * 取得 cache key
     * @param $key
     * @param array $args
     * @return string
     */
    private function getCacheKey($key, $args = [])
    {
        $str = $key;
        if (!empty($args)) {
            foreach ($args as $arg) {
                $str = sprintf($str, $arg);
            }
        }

        return $str;
    }

    private function magentoProducts($products)
    {
        foreach ($products as $index => $row) {
            $query = new QueryParameter();
            $query->no = $row->id;
            $query->source = ProjectConfig::MAGENTO;
            $product = $this->product($query);
            if (!empty($product)) {
                $products[$index] = $product;
            }
        }
        return $products;
    }

    /**
     * 清除 layout 中 magento 商品
     * @param $parameter
     */
    public function cleanLayoutProduct($parameter)
    {
        if($parameter->source == ProjectConfig::MAGENTO) {
            $this->cityPass->updateMagentoProduct($parameter->no);
        } else {

        }

    }
}
