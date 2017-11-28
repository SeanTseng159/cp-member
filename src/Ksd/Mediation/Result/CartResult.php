<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/1
 * Time: 下午 2:32
 */

namespace Ksd\Mediation\Result;


use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Config\ProjectConfig;


class CartResult
{
    use ObjectHelper;

    /**
     * magento 購物車建置
     * @param $result
     * @param $totalResult
     * @param $coupon
     */
    public function magento($result, $totalResult, $coupon=null)
    {
        $this->id = $this->arrayDefault($result, 'id');
        $this->items = [];
        foreach ($this->arrayDefault($result, 'items', []) as $item) {
            $row = new ProductResult();
            $row->source = ProjectConfig::MAGENTO;
            $row->itemId = $this->arrayDefault($item, 'item_id');
            $row->id = $this->arrayDefault($item, 'sku');
            $row->name = $this->arrayDefault($item, 'name');
            $row->spec = $this->arrayDefault($item, 'spec', '');
            $row->qty = $this->arrayDefault($item, 'qty');
            $row->price = $this->arrayDefault($item, 'price');
            $row->additionals = $this->arrayDefault($item, 'additionals', '');
            $row->imageUrl = $this->arrayDefault($item, 'extension_attributes', '')['image_url'];
            $row->purchase = $this->arrayDefault($item, ' purchase', '');
            $this->items[] = $row;
        }

        $this->itemTotal = $this->arrayDefault($result, 'items_count', 0);
        $this->totalAmount = $this->arrayDefault($totalResult, 'subtotal', 0);
        $this->useCoupon['id'] = $this->arrayDefault($totalResult, 'coupon_code', '');
        $this->useCoupon['name'] = $this->arrayDefault($coupon, 'name', '');
        $this->useCoupon['method'] = $this->arrayDefault($coupon, 'name', '');
        $this->discountAmount = $this->arrayDefault($totalResult, 'discount_amount', 0);
        $this->discountTotal = $this->arrayDefault($totalResult, 'subtotal_with_discount', 0);
        $this->payAmount = $this->arrayDefault($totalResult, 'grand_total', 0);
        $this->shipmentAmount = $this->arrayDefault($totalResult, 'shipping_amount', 0);
        $this->shipmentFree = $this->arrayDefault($totalResult, 'shipping_discount_amount', 0);
    }
    /**
     * 處理 city pass 資料建置
     * @param $result
     */
    public function cityPass($result)
    {
        $this->id = $this->arrayDefault($result, 'id');
        $this->items = [];
        foreach ($this->arrayDefault($result, 'items', []) as $item) {
            $row = new ProductResult();
            $row->source = ProjectConfig::CITY_PASS;
            $row->id = $this->arrayDefault($item, 'id');
            $row->name = $this->arrayDefault($item, 'name');
            $row->spec = $this->arrayDefault($item, 'spec');
            $row->qty = $this->arrayDefault($item, 'quantity');
            $row->price = $this->arrayDefault($item, 'price');
            $row->additionals = $this->arrayDefault($item, 'additionals');
            $row->imageUrl = $this->arrayDefault($item, 'imageUrl');
            $row->purchase = $this->arrayDefault($item, ' purchase');
            $this->items[] = $row;
        }
        $this->itemTotal = $this->arrayDefault($result, 'itemTotal', 0);
        $this->totalAmount = $this->arrayDefault($result, 'totalAmount', 0);
        $this->useCoupon = $this->arrayDefault($result, 'useCoupon');
        $this->discountAmount = $this->arrayDefault($result, 'discountAmount', 0);
        $this->discountTotal = $this->arrayDefault($result, 'discountTotal');
        $this->payAmount = $this->arrayDefault($result, 'payAmount', 0);
        $this->shipmentAmount = $this->arrayDefault($result, 'shipmentAmount', 0);
        $this->shipmentFree = $this->arrayDefault($result, 'shipmentFree', 0);
    }




}