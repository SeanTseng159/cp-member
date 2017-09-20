<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/1
 * Time: 下午 2:32
 */

namespace Ksd\Mediation\Result;


use Ksd\Mediation\Helper\ObjectHelper;

class CartResult
{
    use ObjectHelper;

    /**
     * magento 購物車建置
     * @param $result
     */
    public function magento($result)
    {
        $totalAmount = 0;
        $this->id = $this->arrayDefault($result, 'id');
        $this->items = [];
        foreach ($this->arrayDefault($result, 'items', []) as $item) {
            $row = new ProductResult();
            $row->source = 'magento';
            $row->itemId = $this->arrayDefault($item, 'item_id');
            $row->id = $this->arrayDefault($item, 'sku');
            $row->name = $this->arrayDefault($item, 'name');
            $row->qty = $this->arrayDefault($item, 'qty');
            $row->price = $this->arrayDefault($item, 'price');
            $this->items[] = $row;
            $totalAmount += intval($row->price) * intval($row->qty);
        }
        $this->useCoupon = new \stdClass();
        $this->itemTotal = $this->arrayDefault($result, 'items_count', 0);
        $this->totalAmount = $totalAmount;
        $this->discountAmount = 0;
        $this->payAmount = $totalAmount;
    }
}