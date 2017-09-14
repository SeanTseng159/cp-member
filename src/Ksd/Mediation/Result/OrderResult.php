<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 03:02
 */

namespace Ksd\Mediation\Result;

use Ksd\Mediation\Helper\ObjectHelper;

class OrderResult
{
    use ObjectHelper;
    public function magento($result, $isDetail = false)
    {
        $this->source = 'magento';

        if(!$isDetail) {
            $this->orderNo = $this->arrayDefault($result, 'increment_id');
            $this->orderAmount = $this->arrayDefault($result, 'total_paid');
            $this->orderStatus = $this->arrayDefault($result, 'status');
            $this->orderDate = $this->arrayDefault($result, 'created_at');
            $this->payment = $this->arrayDefault($result, 'payment');
            $this->shippingDescription = $this->arrayDefault($result, 'shipping_description');
            $this->shippingAmount = $this->arrayDefault($result, 'shipping_amount');


            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                $row = new ProductResult();
                $row->source = 'magento';
                $row->no = $this->arrayDefault($item, 'item_id');
                $row->id = $this->arrayDefault($item, 'sku');
                $row->name = $this->arrayDefault($item, 'name');
                $row->spec = $this->arrayDefault($item, 'product_type');
                $row->quantity = $this->arrayDefault($item, 'qty_ordered');
                $row->price = $this->arrayDefault($item, 'price');
                $row->status = $this->arrayDefault($item, 'status');
                $row->imageUrl = null;

                $this->items[] = $row;

            }
        }else{
            $this->no = $this->arrayDefault($result, 'item_id');
            $this->amount = $this->arrayDefault($result, 'row_total');
            $this->status = null;
            $this->date = $this->arrayDefault($result, 'created_at');
            $this->payment = null;
            $this->shipping = null;
            $this->discount = $this->arrayDefault($result, 'discount_amount');
            $this->quantity = $this->arrayDefault($result, 'qty_ordered');
            $this->items = [];
            $this->items['id'] = $this->arrayDefault($result, 'sku');
            $this->items['name'] = $this->arrayDefault($result, 'name');
            $this->items['place'] = null;
            $this->items['address'] = null;
            $this->items['status'] = null;
            $this->items['imageUrls'] = null;
            $this->items['imageUrls']['generalPath']  = null;
            $this->items['imageUrls']['thumbanailPath']  = null;

        }

    }
}