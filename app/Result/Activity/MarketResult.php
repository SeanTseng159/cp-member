<?php
/**
 * User: lee
 * Date: 2018/12/07
 * Time: 上午 10:03
 */

namespace App\Result\Activity;

use App\Config\BaseConfig;
use App\Result\BaseResult;
use App\Traits\MarketHelper;

class MarketResult extends BaseResult
{
    use MarketHelper;

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
        $result->banner = $this->getBanner($data->banner);
        $result->shareUrl = $this->webHost . 'activity/market/' . $data->id;
        $result->rule = $this->getLowerCondition($data->condition_type, $data->offer_type, $data->conditions);
        $result->products = $this->getProducts($data->products);

        return $result;
    }

    /**
     * 取 banner
     * @param $data
     */
    public function getBanner($banner)
    {
        if (!$banner) return '';

        return $this->backendHost . $banner->folder . $banner->filename;
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
