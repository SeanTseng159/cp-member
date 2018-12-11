<?php
/**
 * User: lee
 * Date: 2018/12/07
 * Time: 上午 10:03
 */

namespace App\Result\Activity;

use App\Config\BaseConfig;
use App\Result\BaseResult;

class MarketResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取資料
     * @param $data
     */
    public function get($id)
    {
        // if (!$categories) return [];

        $result = new \stdClass;
        $result->id = $id;
        $result->title = '養生雞湯-雞湯包';
        $result->period = '2018-9-5 10:00:10 ~ 2019-9-11 23:59:59';
        $result->description = '全館商品，買越多省越多！';
        $result->banner = 'https://i.ytimg.com/vi/0Rk5jj3gs74/maxresdefault.jpg';
        $result->shareUrl = $this->webHost . 'activity/market/' . $result->id;
        $result->rule = $this->getRule($id);
        $result->products = $this->getProducts();

        return $result;
    }

    /**
     * 取規格
     * @param $data
     */
    public function getRule($id)
    {
        $rule = new \stdClass;

        if ($id == 1) {
            $rule->name = '任選3件 $2499';
            $rule->type = 'FQFP';
            $rule->value1 = 3;
            $rule->value2 = 2499;
        }
        elseif ($id == 2) {
            $rule->name = '滿2件折100元';
            $rule->type = 'DQFP';
            $rule->value1 = 2;
            $rule->value2 = 100;
        }
        elseif ($id == 3) {
            $rule->name = '滿399元折50元';
            $rule->type = 'DPFQ';
            $rule->value1 = 399;
            $rule->value2 = 50;
        }
        elseif ($id == 4) {
            $rule->name = '滿399元打95折';
            $rule->type = 'DPFD';
            $rule->value1 = 399;
            $rule->value2 = 0.95;
        }
        else {
            $rule->name = '滿2件打98折';
            $rule->type = 'DQFD';
            $rule->value1 = 2;
            $rule->value2 = 0.98;
        }

        return $rule;
    }

    /**
     * 取 Products
     * @param $data
     */
    public function getProducts()
    {
        $newProducts = [];

        for ($i=1; $i < 10; $i++) {
            $newProducts[] = $this->getProduct($i);
        }

        return $newProducts;
    }

    /**
     * 取 Product
     * @param $data
     */
    public function getProduct($i)
    {
        $r = rand(0, 1);

        if ($r === 1) {
            $product = new \stdClass;
            $product->id = 275;
            $product->name = '味覺糖';
            $product->price = 30;
            $product->salePrice = 20;
            $product->imgUrl = 'https://devbackend.citypass.tw/storage/prod/15/2dc51e00c5c0dd546d402653e57a9ab1_s.png';
            $product->stock = rand(10, 20);
            $product->maxQuantity = 10;
            $product->spec = $this->getSpec($r);
            $product->specPrice = $this->getPrice($r);
        }
        else {
            $product = new \stdClass;
            $product->id = 275;
            $product->name = '味覺糖';
            $product->price = 20;
            $product->salePrice = 10;
            $product->imgUrl = 'https://devbackend.citypass.tw/storage/prod/15/2dc51e00c5c0dd546d402653e57a9ab1_s.png';
            $product->stock = rand(10, 20);
            $product->maxQuantity = 10;
            $product->spec = $this->getSpec($r);
            $product->specPrice = $this->getPrice($r);
        }

        return $product;
    }

    /**
     * 取 規格
     * @param $data
     */
    public function getSpec($i)
    {
        if ($i === 1) {
            $spec = new \stdClass;
            $spec->id = 413;
            $spec->name = '香蕉口味';
        }
        else {
            $spec = new \stdClass;
            $spec->id = 412;
            $spec->name = '草莓口味';
        }

        return $spec;
    }

    /**
     * 取 票種
     * @param $data
     */
    public function getPrice($i)
    {
        if ($i === 1) {
            $price = new \stdClass;
            $price->id = 472;
            $price->name = '香蕉口味';
        }
        else {
            $price = new \stdClass;
            $price->id = 471;
            $price->name = '草莓口味';
        }

        return $price;
    }
}
