<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/17
 * Time: 下午 2:42
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Cache\Redis;
use Ksd\Mediation\Magento\Product;
use Ksd\Mediation\Result\Collection;

class ProductRepository
{
    protected $redis;
    protected $magento;
    protected $tpass;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->magento = new Product();
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
        if (empty($categories)) {
            $result = $this->redis->remember("product:category:all", 3600, function () {
                $magentoProducts = $this->magento->products();
                $tpassProducts = [];//$this->tpass->products();
                $result = array_diff($magentoProducts, $tpassProducts);
                return $result;
            });
        } else {
            foreach ($categories as $category) {
                $row = $this->redis->remember("product:category:$category", 3600, function () use ($category) {
                    $magentoProducts = $this->magento->products($category);
                    $tpassProducts = [];//$this->tpass->products($category);
                    $result = array_diff($magentoProducts, $tpassProducts);
                    return $result;
                });
                $result = array_merge($result, $row);
            }
        }

        return new Collection($result, $parameter);
    }

    public function product($parameter)
    {
        $id = $parameter->no;
        return $this->redis->remember("product:id:$id", 3600, function () use ($id) {
            $product = $this->magento->product($id);
            if (empty($product)) {
                $product = null; //$this->tpass
            }
            return $product;
        });
    }
}