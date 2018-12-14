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
    public function get($data)
    {
        if (!$data) return [];

        $result = new \stdClass;
        $result->id = $data->id;
        $result->title = $data->title;
        $result->period = sprintf('%s ~ %s', $data->onsale_time, $data->offsale_time);
        $result->description = $data->sub_title;
        $result->banner = 'https://i.ytimg.com/vi/0Rk5jj3gs74/maxresdefault.jpg';
        $result->shareUrl = $this->webHost . 'activity/market/' . $data->id;
        $result->rule = $this->getRule($data->condition_type, $data->offer_type, $data->condition);
        $result->products = $this->getProducts($data->products);

        return $result;
    }

    /**
     * 取規格
     * @param $data
     */
    public function getRule($type, $offerType, $condition)
    {
        $rule = new \stdClass;

        $nameType = $this->getRuleNameAndType($type, $offerType, $condition);
        $rule->name = $nameType['name'];
        $rule->type = $nameType['type'];
        $rule->value1 = $condition->condition;
        $rule->value2 = $this->getOffer($offerType, $condition->offer);

        return $rule;
    }

    /**
     * 取規則名稱
     * @param $data
     */
    private function getRuleNameAndType($conditionType, $offerType, $condition)
    {
        $name = '';
        $type = '';

        switch ($conditionType) {
            case 1:
                if ($offerType === 1) {
                    $name = '滿%s元 折%s元';
                    $type = 'DPFQ';
                }
                elseif ($offerType === 2) {
                    $name = '滿%s元 打%s折';
                    $type = 'DPFD';
                }
                break;
            case 2:
                if ($offerType === 1) {
                    $name = '滿%s件 折%s元';
                    $type = 'DQFP';
                }
                elseif ($offerType === 2) {
                    $name = '滿%s件 打%s折';
                    $type = 'DQFD';
                }
                break;
        }

        $offer = $this->getOffer($offerType, $condition->offer);

        return [
            'name' => sprintf($name, $condition->condition, $offer),
            'type' => $type
        ];
    }

    /**
     * 取優惠值
     * @param $data
     */
    private function getOffer($type, $value)
    {
        if ($type === 2) {
            return round($value, 2) * 100;
        }

        return floor($value);
    }

    /**
     * 取 Products
     * @param $data
     */
    public function getProducts($products = [])
    {
        if (!$products) return [];

        $newProducts = [];
        foreach ($products as $product) {
            $newProducts[] = $this->getProduct($product);
        }

        return $newProducts;
    }

    /**
     * 取 Product
     * @param $data
     */
    public function getProduct($prod)
    {
        if (!$prod) return null;

        $product = new \stdClass;
        $product->id = $prod->prod_id;
        $product->name = $prod->prod_name;
        $product->price = $prod->prod_spec_price_list;
        $product->salePrice = $prod->marketPrice;
        $product->imgUrl = ($prod->img) ? $this->backendHost . $prod->img->img_thumbnail_path : '';
        $product->stock = $prod->marketStock;
        $product->maxQuantity = $prod->marketStock;
        $product->spec = $this->getSpec($prod);
        $product->specPrice = $this->getPrice($prod);

        return $product;
    }

    /**
     * 取 規格
     * @param $data
     */
    public function getSpec($prod)
    {
        $spec = new \stdClass;
        $spec->id = $prod->prod_spec_id;
        $spec->name = $prod->prod_spec_name;

        return $spec;
    }

    /**
     * 取 票種
     * @param $data
     */
    public function getPrice($prod)
    {
        $price = new \stdClass;
        $price->id = $prod->prod_spec_price_id;
        $price->name = $prod->prod_spec_price_name;

        return $price;
    }
}
