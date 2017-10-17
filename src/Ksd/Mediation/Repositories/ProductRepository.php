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
use Ksd\Mediation\Magento\Product as MagentoProduct;
use Ksd\Mediation\CityPass\Product as CityPassProduct;
use Ksd\Mediation\Result\Collection;
use Ksd\Mediation\Result\Product\ProductIndexResult;

class ProductRepository extends BaseRepository
{

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
        $result = $this->redis->remember("categories", 3600, function () use ($id) {
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
    public function products($categories = null, $parameter = null)
    {
        $result = [];
        if (empty($categories) && empty($parameter->categories())) {
            $result = $this->redis->remember("product:category:all", 3600, function () {
                $magentoProducts = $this->magento->all();
                $cityPassProducts = $this->cityPass->all();
                return array_merge($magentoProducts, $cityPassProducts);
            });
        } else {
            $source = ProjectConfig::MAGENTO;
            foreach ($categories as $category) {
                $row = $this->redis->remember("$source:product:category:$category", 3600, function () use ($category) {
                    return $this->magento->all($category);
                });
                $result = array_merge($result,$row);
            }

            $source = ProjectConfig::CITY_PASS;
            $categoryKey = join('_', $parameter->categories());
            $cityPassProducts = $this->redis->remember("$source:product:category:$categoryKey", 3600, function () use ($parameter) {
                return $this->cityPass->tags($parameter->categories());
            });
            $result = array_merge($result, $cityPassProducts);
        }

        return new Collection($result, $parameter);
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $parameter
     * @return mixed
     */
    public function product($parameter)
    {
        $id = $parameter->no;
        $source = $parameter->source;
        $product = $this->redis->remember("$source:product:id:$id", 3600, function () use ($source,$id) {
            $product = null;
            if($source == ProjectConfig::MAGENTO) {
                $product = $this->magento->find($id);
            } else {
                $product = $this->cityPass->find($id);
            }
            return $product;
        });
        $this->createIndex($source, $product);
        return $product;
    }

    /**
     * 根據 關鍵字 搜尋 取得商品列表
     * @param $parameter
     * @return array
     */
    public function search($parameter)
    {

        $magento = $this->magento->search($parameter);
        $cityPass = $this->cityPass->search($parameter);;
        return [
            ProjectConfig::MAGENTO => $magento,
            ProjectConfig::CITY_PASS => $cityPass
        ];

    }

    /**
     * 建立索引檔
     * @param $source
     * @param $product
     */
    public function createIndex($source, $product)
    {
        $cacheKey = sprintf('%s:product:index', $source);
        $indexResults = $this->redis->get($cacheKey);
        if (empty($indexResults)) {
            $indexResults = [];
        }

        $productIndex = new ProductIndexResult();
        if($source == ProjectConfig::MAGENTO) {
            $productIndex->magneto($product);
        }

        $hasIndex = false;

        foreach ($indexResults as $row) {
            if (!empty($row->find($productIndex->id))) {
                $hasIndex = true;
                break;
            }
        }
        if (!$hasIndex) {
            $indexResults = array_merge($indexResults, [$productIndex]);
        }

        $this->redis->set($cacheKey, $indexResults, 3600 * 24);

    }
}