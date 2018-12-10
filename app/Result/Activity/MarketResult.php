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
            $rule->name = '任選2件 $2499';
            $rule->type = 'FQFP';
            $rule->value1 = 2;
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
        $product = new \stdClass;
        $product->id = $i;
        $product->name = '養生雞湯';
        $product->price = 300;
        $product->salePrice = 250;
        $product->imgUrl = ($i * 3 % 2 === 0) ? 'https://www.anyongfresh.com/website/uploads/fckeditor/anyongfresh/image/%E7%B2%89%E5%85%89%E9%A6%99%E8%8F%87%E9%9B%9E%E6%B9%AF4.jpg' : 'https://s.yimg.com/zp/images/06F6054F0CC0CBD73B7D4C959DFEF5236B8DB779';
        $product->stock = rand(100, 300);
        $product->maxQuantity = 10;
        $product->spec = $this->getSpec($i);
        $product->price = $this->getPrice($i);

        return $product;
    }

    /**
     * 取 規格
     * @param $data
     */
    public function getSpec($i)
    {
        $spec = new \stdClass;
        $spec->id = $i;
        $spec->name = ($i * 3 % 2 === 0) ? '香菇雞湯包' : '金針雞湯包';

        return $spec;
    }

    /**
     * 取 票種
     * @param $data
     */
    public function getPrice($i)
    {
        $price = new \stdClass;
        $price->id = $i;
        $price->name = ($i * 3 % 2 === 0) ? '大包' : '小包';

        return $price;
    }
}
