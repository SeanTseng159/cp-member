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

class ProductRepository extends BaseRepository
{

    public function __construct()
    {
        $this->redis = new Redis();
        $this->magento = new MagentoProduct();
        $this->cityPass = new CityPassProduct();
    }

    public function categories($id = 1)
    {
        $result = $this->redis->remember("categories", 3600, function () use ($id) {
            $magentoProducts = $this->magento->categories($id);
            $tpassProducts = [];//$this->tpass->products($category);
            return [$magentoProducts, $tpassProducts];
        });
        return $result;
    }

    public function products($categories = null, $parameter = null)
    {
        $result = [];
        if (empty($categories) && empty($parameter->categories())) {
            $result = $this->redis->remember("product:category:all", 3600, function () {
                $magentoProducts = $this->magento->products();
                $cityPassProducts = $this->cityPass->products();
                return array_merge($magentoProducts, $cityPassProducts);
            });
        } else {
            $source = ProjectConfig::MAGENTO;
            foreach ($categories as $category) {
                $row = $this->redis->remember("$source:product:category:$category", 3600, function () use ($category) {
                    return $this->magento->products($category);
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

    public function product($parameter)
    {
        $id = $parameter->no;
        $source = $parameter->source;
        return $this->redis->remember("$source:product:id:$id", 3600, function () use ($source,$id) {
            $product = null;
            if($source == ProjectConfig::MAGENTO) {
                $product = $this->magento->product($id);
            } else {
                $product = $this->cityPass->product($id);
            }
            return $product;
        });
    }
}