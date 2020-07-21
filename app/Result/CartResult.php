<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Result;

use App\Result\BaseResult;
use App\Traits\CartHelper;

class CartResult extends BaseResult
{
    use CartHelper;


    // 購物車類型
    private $cartType = 'member';
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
    // 免費金額
    private $shipmentFree = 0;
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

    public function getSimplifyItems($items, $hasPurchase = true)
    {
        $newItems = [];

        foreach ($items as $item) {
            $newItems[] = $this->getSimplifyItem($item, $hasPurchase);
        }

        return $newItems;
    }

    /**
     * 簡化資料
     * @param $product
     * @param $hasPurchase
     * @param bool $isDetail
     */
    public function getSimplifyItem($item, $hasPurchase = true)
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
        unset($item->groups);

        $item->additional = $this->getSimplifyAdditional($item->additional);

        if ($hasPurchase) {
            $item->purchase = $this->getSimplifyItems($item->purchase, false);
        }

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
     * 處理購物車資料
     * @param $cartType ['member', 'market', 'buyNow', 'guest']
     * @param $product
     * @param $isDetail
     * @param $promotion [App\Repositories\Ticket\Promotion]
     */
    public function get($cartType = 'member', $products, $isDetail = false, $promotion = null)
    {
        // $this->cartType = $cartType;

        if ($promotion) {
            // 有優惠條件購物車
            $result = new \stdClass;
            $result->type = $cartType;
            $result->items = ($products) ? $this->getItems($products, $isDetail, true) : [];
            $result->totalQuantity = $this->totalQuantity;
            $result->totalAmount = $this->totalAmount;
            $result->discountAmount = $this->calcDiscountAmount($promotion, $result->totalAmount, $result->totalQuantity);
            $result->discountTotalAmount = $result->totalAmount - $result->discountAmount;
            $result->shippingFee = $this->calcMarketShippingFee($promotion->shipping_type, $promotion->shipping, $result->totalQuantity, $result->discountTotalAmount);
            $result->shipmentFree = $this->shipmentFree;
            $result->payAmount = $result->discountTotalAmount + $result->shippingFee;
            $result->canCheckout = ($products) ? true : false;
            $result->hasPhysical = $this->hasPhysical;
            $result->promotion = $this->getFitCondition($promotion, $result->totalAmount, $result->totalQuantity);
        }
        else {
            // 一般購物車
            $result = new \stdClass;
            $result->type = $cartType;
            $result->items = ($products) ? $this->getItems($products, $isDetail, true) : [];
            $result->totalQuantity = $this->totalQuantity;
            $result->totalAmount = $this->totalAmount;
            $result->discountAmount = $this->discountAmount;
            $result->discountTotalAmount = $this->totalAmount;
            $result->shippingFee = $this->shippingFee;
            $result->shipmentFree = $this->shipmentFree;
            $result->payAmount = $this->totalAmount + $this->shippingFee;
            $result->canCheckout = ($products) ? true : false;
            $result->hasPhysical = $this->hasPhysical;
            $result->promotion = null;
        }

        return $result;
    }

    /**
     * 處理購物車資料
     * @param $cartType ['member', 'market', 'buyNow', 'guest']
     * @param $product
     * @param $isDetail
     * @param $promotion [App\Repositories\Ticket\Promotion]
     */
    public function getCartDetail($cartType = 'member', $products, $shippingFeeDetail)
    {
        $this->cartType = $cartType;

        if ($this->cartType === 'guest') {
            // 訪客購物車

            // 處理商品
            $items = ($products) ? $this->getItems($products, true, true) : [];
            // 計算運費
            $this->calcShippingFee($shippingFeeDetail);

            $result = new \stdClass;
            $result->type = $this->cartType;
            $result->items = $items;
            $result->totalQuantity = $this->totalQuantity;
            $result->totalAmount = $this->totalAmount;
            $result->discountAmount = $this->discountAmount;
            $result->discountTotalAmount = $this->totalAmount - $this->discountAmount;
            $result->shippingFee = $this->shippingFee;
            $result->shipmentFree = $this->shipmentFree;
            $result->payAmount = $result->discountTotalAmount + $this->shippingFee;
            $result->canCheckout = ($products) ? true : false;
            $result->hasPhysical = $this->hasPhysical;
            $result->promotion = null;
        }

        return $result;
    }


    public function getItems($products, $isDetail = false, $isMainProd = false)
    {
        $items = [];

        foreach ($products as $product) {
            $items[] = $this->getItem($product, $isDetail, $isMainProd);
        }

        return $items;
    }

    /**
     * 取得資料
     * @param $product
     * @param $isDetail
     * @param $isMainProd
     * @param bool $isDetail
     */
    public function getItem($product, $isDetail = false, $isMainProd = false)
    {
        if (!$product) return null;

        $prod = new \stdClass;
        $prod->id = $product->prod_id;
        $prod->name = $product->prod_name;
        $prod->quantity = (int) $product->quantity;
        $prod->price = $product->prod_spec_price_value;
        $prod->imageUrl = ($product->img) ? $this->backendHost . $product->img->img_thumbnail_path : '';
        $prod->additional = $this->getAdditional($product, $isDetail);

        if ($product->prod_type === 1 || $product->prod_type === 2) {
            $prod->purchase = (isset($product->purchase)) ? $this->getPurchase($product->purchase, $isDetail) : [];
        }

        if ($isDetail) {
            $prod->supplierId = $product->supplier_id;
            $prod->custId = $product->prod_cust_id;
            $prod->type = $product->prod_type;
            $prod->isPhysical = $product->is_physical;
            $prod->catalogId = ($product->prod_type !== 2 && $product->tags) ? $product->tags->where('is_main', 1)->first()->tag_id : 0;
            $prod->categoryId = ($product->prod_type !== 2 && $product->tags) ? $product->tags->where('is_main', 0)->first()->tag_id : 0;
            $prod->api = $product->prod_api;
            $prod->store = $product->prod_store;
            $prod->address = $product->prod_zipcode . $product->full_address;
            $prod->retailPrice = $product->prod_spec_price_list;

            $prod->expireType = $product->prod_expire_type;
            if ($product->prod_expire_type === 1) {
                $date = date('Y-m-d H:i:s');
                $prod->expireStart = $date;
                $prod->expireDue = date('Y-m-d 23:59:59', strtotime($date . " +{$product->prod_expire_daycount} day"));
            }
            else {
                $prod->expireStart = ($product->prod_expire_start == 0) ? NULL : $product->prod_expire_start;
                $prod->expireDue = ($product->prod_expire_due == 0) ? NULL : $product->prod_expire_due;
            }

            $prod->groupExpireType = $product->group_expire_type;
            $prod->groupExpireDue = $product->group_expire_due;
            // 組合子商品
            $prod->groups = $this->getItems($product->groups, true, false);
        }

        // 主商品才計算
        if ($isMainProd) {
            // 計算運費
            if ($this->cartType !== 'guest') {
                $this->shippingFee += $this->calcShippingFee($product->shippingFees, $product->quantity);
            }

            // 計算全部金額
            $this->totalQuantity += $product->quantity;
            $this->totalAmount += $product->prod_spec_price_value * $product->quantity;

            if (!$this->hasPhysical) $this->hasPhysical = ($product->is_physical) ? true : false;
        }

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
            $additional->type->useExpireStart = ($product->prod_spec_price_use_note_time_start == 0) ? NULL : $product->prod_spec_price_use_note_time_start;
            $additional->type->useExpireDue = ($product->prod_spec_price_use_note_time_end == 0) ? NULL : $product->prod_spec_price_use_note_time_end;
        }

        $additional->usageTime = '';
        $additional->remark = '';

        return $additional;
    }

    /**
     * 取得加購商品
     * @param $products
     * @return array
     */
    private function getPurchase($products, $isDetail)
    {
        if (!$products) return [];

        $newPurchase = [];
        foreach ($products as $product) {
            $newPurchase[] = $this->getItem($product, $isDetail, true);
        }

        return $newPurchase;
    }

    /**
     * 計算運費
     * @param $products
     * @return array
     */
    private function calcShippingFee($shippingFeeDetail)
    {

        if ($this->hasPhysical) {
            // 有實體商品

            if ($shippingFeeDetail) {
                $this->shippingFee = $shippingFeeDetail->shipment_amount;
                $this->shipmentFree = $shippingFeeDetail->shipment_free;

                // 滿額免運
                if ($shippingFeeDetail->option === 2) {
                    $discountTotalAmount = $this->totalAmount - $this->discountAmount;
                    if ($discountTotalAmount >= $this->shipmentFree) {
                        $this->shippingFee = 0;
                    }

                }
            }
            else {
                // 沒有設定運費，給預設值
                $this->shippingFee = 65;
                $this->shipmentFree = 999999;
            }
        }
    }
}
