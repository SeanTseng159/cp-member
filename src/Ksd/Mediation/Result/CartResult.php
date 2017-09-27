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
     */
    public function magento($result, $totalResult)
    {
        $this->id = $this->arrayDefault($result, 'id');
        $this->items = [];
        foreach ($this->arrayDefault($result, 'items', []) as $item) {
            $row = new ProductResult();
            $row->source = ProjectConfig::MAGENTO;
            $row->itemId = $this->arrayDefault($item, 'item_id');
            $row->id = $this->arrayDefault($item, 'sku');
            $row->name = $this->arrayDefault($item, 'name');
            $row->qty = $this->arrayDefault($item, 'qty');
            $row->price = $this->arrayDefault($item, 'price');
            $this->items[] = $row;
        }
        $this->useCoupon = new \stdClass();
        $this->itemTotal = $this->arrayDefault($result, 'items_count', 0);
        $this->totalAmount = $this->arrayDefault($totalResult, 'subtotal', 0);
        $this->discountAmount = $this->arrayDefault($totalResult, 'discount_amount', 0);
        $this->payAmount = $this->arrayDefault($totalResult, 'grand_total', 0);
    }
    /**
     * 處理 city pass 資料建置
     * @param $result
     * @param bool $isDetail
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
            $row->qty = $this->arrayDefault($item, 'quantity');
            $row->price = $this->arrayDefault($item, 'price');
            $row->additionals = $this->arrayDefault($item, 'additionals');
            $row->imageUrl = $this->arrayDefault($item, 'imageUrl');
            $row->purchase = $this->arrayDefault($item, ' purchase');
            $this->items[] = $row;
        }
        $this->useCoupon = new \stdClass();
        $this->itemTotal = $this->arrayDefault($result, 'itemTotal', 0);
        $this->totalAmount = $this->arrayDefault($result, 'totalAmount', 0);
        $this->discountAmount = $this->arrayDefault($result, 'discountAmount', 0);
        $this->payAmount = $this->arrayDefault($result, 'payAmount', 0);
    }




}