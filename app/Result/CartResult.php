<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result;

use App\Result\BaseResult;
use App\Traits\CheckoutHelper;

class CartResult extends BaseResult
{
    use CheckoutHelper;

    // 總數量
    private $totalQuantity = 0;
    // 總金額
    private $totalAmount = 0;
    // 折扣金額
    private $discountAmount = 0;
    // 折扣後總金額
    private $discountTotalAmount = 0;
    // 運費
    private $shippingFee = 0;
    // 實際付款金額
    private $payAmount = 0;
    // 是否有實體商品
    private $hasPhysical = false;

    /**
     * 簡化資料
     * @param $product
     * @param object
     */
    public function simplify($cart)
    {
        $cart->items = $this->getSimplifyItems($cart->items);

        return $cart;
    }

    public function getSimplifyItems($items)
    {
        $newItems = [];

        foreach ($items as $item) {
            $newItems[] = $this->getSimplifyItem($item);
        }

        return $newItems;
    }

    /**
     * 簡化資料
     * @param $product
     * @param $isDetail
     * @param bool $isDetail
     */
    public function getSimplifyItem($item)
    {
        unset($item->supplierId);
        unset($item->custId);
        unset($item->type);
        unset($item->isPhysical);
        unset($item->catalogId);
        unset($item->categoryId);
        unset($item->api);
        unset($item->store);
        unset($item->address);
        unset($item->expireType);
        unset($item->expireStart);
        unset($item->expireDue);
        unset($item->groupExpireType);
        unset($item->groupExpireDue);

        $item->additional = $this->getSimplifyAdditional($item->additional);

        return $item;
    }

    /**
     * 簡化規格
     * @param $product
     * @param $isDetail
     * @return object
     */
    private function getSimplifyAdditional($additional)
    {
        unset($additional->type->useType);
        unset($additional->type->useValue);
        unset($additional->type->useExpireStart);
        unset($additional->type->useExpireDue);

        return $additional;
    }

    /**
     * 取得資料
     * @param $product
     * @param object
     */
    public function get($products, $isDetail = false)
    {
        $result = new \stdClass;
        $result->items = ($products) ? $this->getItems($products, $isDetail) : [];
        $result->totalQuantity = $this->totalQuantity;
        $result->totalAmount = $this->totalAmount;
        $result->discountAmount = $this->discountAmount;
        $result->discountTotalAmount = $this->totalAmount;
        $result->shippingFee = $this->shippingFee;
        $result->payAmount = $this->totalAmount + $this->shippingFee;
        $result->canCheckout = ($products) ? true : false;
        $result->hasPhysical = $this->hasPhysical;

        return $result;
    }

    public function getItems($products, $isDetail = false)
    {
        $items = [];

        foreach ($products as $product) {
            $items[] = $this->getItem($product, $isDetail);
        }

        return $items;
    }

    /**
     * 取得資料
     * @param $product
     * @param $isDetail
     * @param bool $isDetail
     */
    public function getItem($product, $isDetail = false)
    {
        if (!$product) return null;

        $prod = new \stdClass;
        $prod->id = $product->prod_id;
        $prod->name = $product->prod_name;
        $prod->quantity = (int) $product->quantity;
        $prod->price = $product->prod_spec_price_value;
        $prod->imageUrl = ($product->img) ? $this->backendHost . $product->img->img_thumbnail_path : '';
        $prod->additional = $this->getAdditional($product, $isDetail);
        $prod->purchase = [];

        if ($isDetail) {
            $prod->supplierId = $product->supplier_id;
            $prod->custId = $product->prod_cust_id;
            $prod->type = $product->prod_type;
            $prod->isPhysical = $product->is_physical;
            $prod->catalogId = ($product->tags) ? $product->tags->where('is_main', 1)->first()->tag_id : 0;
            $prod->categoryId = ($product->tags) ? $product->tags->where('is_main', 0)->first()->tag_id : 0;
            $prod->api = $product->prod_api;
            $prod->store = $product->prod_store;
            $prod->address = $product->prod_zipcode . $product->full_address;
            $prod->expireType = $product->prod_expire_type;
            $prod->expireStart = $product->prod_expire_start;
            $prod->expireDue = $product->prod_expire_due;
            $prod->groupExpireType = $product->group_expire_type;
            $prod->groupExpireDue = $product->group_expire_due;
        }

        // 計算運費
        $this->shippingFee += $this->calcShippingFee($product->shippingFees, $product->quantity);

        // 計算全部金額
        $this->totalQuantity += $product->quantity;
        $this->totalAmount += $product->prod_spec_price_value * $product->quantity;

        if (!$this->hasPhysical) $this->hasPhysical = ($product->is_physical) ? true : false;

        return $prod;
    }

    /**
     * 取得規格
     * @param $product
     * @param $isDetail
     * @return object
     */
    private function getAdditional($product, $isDetail)
    {
        $additional = new \stdClass;

        // 規格
        $additional->spec = new \stdClass;
        $additional->spec->id = $product->prod_spec_id;
        $additional->spec->name = $product->prod_spec_name;

        // 種類
        $additional->type = new \stdClass;
        $additional->type->id = $product->prod_spec_price_id;
        $additional->type->name = $product->prod_spec_price_name;

        if ($isDetail) {
            $additional->type->useType = $product->prod_spec_price_use_note;
            $additional->type->useValue = $product->prod_spec_price_use_note_value;
            $additional->type->useExpireStart = $product->prod_spec_price_use_note_time_start;
            $additional->type->useExpireDue = $product->prod_spec_price_use_note_time_end;
        }

        $additional->usageTime = '';
        $additional->remark = '';

        return $additional;
    }
}
