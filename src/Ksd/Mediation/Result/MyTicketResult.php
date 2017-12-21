<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 03:39
 */

namespace Ksd\Mediation\Result;

use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Config\ProjectConfig;


class MyTicketResult
{
    use ObjectHelper;

    /**
     * 處理 cityPass Layout資料建置
     * @param $result
     * @param bool $isDetail
     */
    public function cityPass($result, $isDetail = false)
    {
        $this->source = ProjectConfig::CITY_PASS;

        if(!$isDetail) {
            $this->orderNo = $this->arrayDefault($result, 'orderNo');
            $this->orderAmount = $this->arrayDefault($result, 'orderAmount');
            $this->orderStatus = $this->arrayDefault($result, 'orderStatus');
            $this->orderDate = $this->arrayDefault($result, 'orderDate');
            $this->payment = $this->arrayDefault($result, 'payment');
            $this->shipping = $this->arrayDefault($result, 'shipping');
            /*            foreach ($this->arrayDefault($result, 'shipping', []) as $shipping) {
                            $this->shipping['name'] = $shipping['name'];
                            $this->shipping['phone'] = $shipping['phone'];
                            $this->shipping['postcode'] = $shipping['postcode'];
                            $this->shipping['address'] = $shipping['address'];
                            $this->shipping['shippingDescription'] = $shipping['shippingDescription'];
                            $this->shipping['shippingAmount'] = $shipping['shippingAmount'];

                        }
            */

            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                $row = [];
                $row['source'] = ProjectConfig::CITY_PASS;
                $row['no'] = $this->arrayDefault($item, 'itemId');
                $row['id'] = $this->arrayDefault($item, 'no');
                $row['name'] = $this->arrayDefault($item, 'name');
                $row['spec'] = $this->arrayDefault($item, 'spec');
                $row['quantity'] = $this->arrayDefault($item, 'quantity');
                $row['price'] = $this->arrayDefault($item, 'price');
                $row['description'] = $this->arrayDefault($item, 'description');
                $row['status'] = $this->arrayDefault($item, 'status');
                $row['discount'] = $this->arrayDefault($item, 'discount');
                $row['imageUrls'] = $this->arrayDefault($item, 'imageUrls');

                $this->items[] = $row;

            }
        }else{
            $this->source = $this->arrayDefault($result, 'source');
            $this->no = $this->arrayDefault($result, 'no');
            $this->amount = $this->arrayDefault($result, 'amount');
            $this->status = $this->arrayDefault($result, 'status');
            $this->date = $this->arrayDefault($result, 'date');
            $this->discount = $this->arrayDefault($result, 'discount_amount');
            $this->quantity = $this->arrayDefault($result, 'qty_ordered');
            $this->items =  $this->arrayDefault($result, 'items');

        }

    }
}