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
            $row->redirectId = $this->getMainItemSku($this->arrayDefault($item, 'sku'));
            $row->name = $this->arrayDefault($item, 'name');
            $row->spec = $this->arrayDefault($item, 'spec');
            $row->qty = $this->arrayDefault($item, 'qty');
            $row->price = $this->arrayDefault($item, 'price');
            $row->additionals = $this->arrayDefault($item, 'additionals') ==='' ? new \stdClass() : $this->arrayDefault($item, 'additionals');
            $row->imageUrl = $this->arrayDefault($item, 'extension_attributes', '')['image_url'];
            $row->purchase = $this->arrayDefault($item, ' purchase', '');

            $this->items[] = $row;
        }

        $this->itemTotal = $this->arrayDefault($result, 'items_count');
        $this->totalAmount = $this->arrayDefault($totalResult, 'subtotal');
        if(is_null($coupon)){
            $this->useCoupon=null;
        }else {
            $this->useCoupon['id'] = $this->arrayDefault($coupon, 'rule_id');
            $this->useCoupon['name'] = $this->arrayDefault($coupon, 'name');
            $this->useCoupon['method'] = $this->arrayDefault($coupon, 'name');
        }
        $this->discountAmount = ceil($this->arrayDefault($totalResult, 'discount_amount'));
        $this->discountTotal = $this->arrayDefault($totalResult, 'subtotal') + ceil($this->arrayDefault($totalResult, 'discount_amount'));
        $this->payAmount = $this->arrayDefault($totalResult, 'grand_total');
        $this->shipmentAmount = $this->arrayDefault($totalResult, 'shipping_amount');
        $this->shipmentFree = $this->arrayDefault($totalResult, 'shipping_discount_amount');
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
            $row->redirectId = $this->arrayDefault($item, 'id');
            $row->name = $this->arrayDefault($item, 'name');
            $row->spec = $this->arrayDefault($item, 'spec');
            $row->qty = $this->arrayDefault($item, 'quantity');
            $row->price = $this->arrayDefault($item, 'price');
            $row->additionals = $this->arrayDefault($item, 'additionals');
            $row->imageUrl = $this->arrayDefault($item, 'imageUrl');
            $row->purchase = $this->arrayDefault($item, 'purchase');
            // $row->statusCode = $this->arrayDefault($item['status'], 'code');
            // $row->statusDesc = $this->arrayDefault($item['status'], 'desc');
            $this->items[] = $row;
        }
        $this->itemTotal = $this->arrayDefault($result, 'itemTotal');
        $this->totalAmount = $this->arrayDefault($result, 'totalAmount');
        if(empty($this->arrayDefault($result, 'useCoupon'))){
            $this->useCoupon = null;
        }else {
            $this->useCoupon = $this->arrayDefault($result, 'useCoupon');
        }
        $this->discountAmount = $this->arrayDefault($result, 'discountAmount');
        $this->discountTotal = $this->arrayDefault($result, 'discountTotal');
        $this->payAmount = $this->arrayDefault($result, 'payAmount');
        $this->shipmentAmount = $this->arrayDefault($result, 'shipmentAmount');
        $this->shipmentFree = $this->arrayDefault($result, 'shipmentFree');
    }

    /**
     * magento 組合商品之主商品sku獲取
     * @param $key
     * @return string
     */
    public function getMainItemSku($key)
    {
        if(!empty($key)) {
            $mainKey = explode("-", $key);
            return $mainKey[0];
        }

    }
}
