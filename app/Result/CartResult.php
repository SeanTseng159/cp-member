<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result;

use App\Result\BaseResult;
use App\Config\BaseConfig;
use App\Traits\CheckoutHelper;

class CartResult extends BaseResult
{
    use CheckoutHelper;

    private $itemTotal = 0;
    private $totalAmount = 0;
    private $discountAmount = 0;
    private $discountTotal = 0;
    private $shippingFee = 0;
    private $payAmount = 0;

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function get($products)
    {
        $result = new \stdClass;
        $result->items = ($products) ? $this->getItems($products) : [];
        $result->itemTotal = $this->itemTotal;
        $result->totalAmount = $this->totalAmount;
        $result->discountAmount = $this->discountAmount;
        $result->discountTotal = $this->totalAmount;
        $result->shippingFee = $this->shippingFee;
        $result->payAmount = $this->payAmount;
        $result->canCheckout = ($products) ? true : false;

        return $result;
    }

    public function getItems($products)
    {
        $items = [];

        foreach ($products as $product) {
            $items[] = $this->getItem($product);
        }

        return $items;
    }

    /**
     * 取得資料
     * @param $product
     * @param bool $isDetail
     */
    public function getItem($product)
    {
        if (!$product) return null;

        $prod = new \stdClass;
        $prod->source = BaseConfig::SOURCE_TICKET;
        $prod->id = $product->prod_id;
        $prod->name = $product->prod_name;
        $prod->quantity = $product->quantity;
        $prod->price = $product->prod_spec_price_value;
        $prod->imageUrl = ($product->img) ? $this->backendHost . $product->img->img_thumbnail_path : '';
        $prod->additional = $this->getAdditional($product);
        $prod->purchase = [];

        // 計算運費
        $this->shippingFee += $this->calcShippingFee($product->shippingFees, $product->quantity);

        // 計算全部金額
        $this->itemTotal += $product->quantity;
        $this->totalAmount += $product->prod_spec_price_value * $product->quantity;

        return $prod;
    }

    /**
     * 取得規格
     * @param $product
     * @return object | null
     */
    private function getAdditional($product)
    {
        $additional = new \stdClass;
        $additional->specId = $product->prod_spec_id;
        $additional->priceId = $product->prod_spec_price_id;
        $additional->spec = $product->prod_spec_price_name;
        $additional->usageTime = '';
        $additional->remark = '';

        return $additional;
    }
}
