<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 03:02
 */

namespace Ksd\Mediation\Result;

use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Magento\Product;
use Ksd\Mediation\Magento\Order;

class OrderResult
{
    use ObjectHelper;
    /**
     * 處理 magento 訂單資料建置
     * @param $result
     * @param bool $isDetail
     */
    public function magento($result, $isDetail = false)
    {
        $this->source = ProjectConfig::MAGENTO;
        $product = new Product();

        if(!$isDetail) {
            $this->orderNo = $this->arrayDefault($result, 'increment_id');
            $this->orderAmount = $this->arrayDefault($result, 'total_paid');
            $this->orderStatus = $this->arrayDefault($result, 'status');
            $this->orderDate = $this->arrayDefault($result, 'created_at');
            $payment = $this->arrayDefault($result, 'payment');
            $this->payment['method'] = $payment['method'];
            $this->shipping = [];
            $ship = $this->arrayDefault($result, 'extension_attributes');
            foreach ($this->arrayDefault($ship, 'shipping_assignments', []) as $shipping) {
                $shipping = $this->arrayDefault($shipping, 'shipping');
                $this->shipping['name'] = $shipping['address']['firstname'] . $shipping['address']['lastname'];
                $this->shipping['phone'] = $shipping['address']['telephone'];
                $this->shipping['postcode'] = $shipping['address']['postcode'];
                $this->shipping['address'] = $shipping['address']['city'].$shipping['address']['street'][0];

            }
            $this->shipping['shippingDescription'] = $this->arrayDefault($result, 'shipping_description');
            $this->shipping['shippingAmount'] = $this->arrayDefault($result, 'shipping_amount');

            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                if($this->arrayDefault($item, 'price') != 0) {
                    $row = [];
                    $row['source'] = ProjectConfig::MAGENTO;
                    $row['no'] = $this->arrayDefault($item, 'item_id');
                    $row['id'] = $this->arrayDefault($item, 'sku');
                    $row['name'] = $this->arrayDefault($item, 'name');
                    $row['spec'] = $this->arrayDefault($item, 'product_type');
                    $row['quantity'] = $this->arrayDefault($item, 'qty_ordered');
                    $row['price'] = $this->arrayDefault($item, 'price');
                    $row['description'] = $this->arrayDefault($result, 'shipping_description');
                    $ordered = $this->arrayDefault($item, 'qty_ordered');
                    $shipped = $this->arrayDefault($item, 'qty_shipped');
                    $refunded = $this->arrayDefault($item, '$qty_refunded');

                    if($shipped !== 0){
                        $row['status'] = $this->shippingStatus($this->arrayDefault($item, 'order_id'),$this->arrayDefault($item, 'sku'));
                    }else if($refunded != 0){
                        $row['status'] = '已退貨';
                    }else{
                        $row['status'] = '處理中';
                    }
                    $row['discount'] = $this->arrayDefault($result, 'discount_amount');
                    $generalPath = $product->find($this->arrayDefault($item, 'sku'))->imageUrls[0]['generalPath'];
                    $thumbnailPath = $product->find($this->arrayDefault($item, 'sku'))->imageUrls[0]['thumbnailPath'];
                    $row['imageUrls']['generalPath'] = $generalPath;
                    $row['imageUrls']['thumbnailPath'] = $thumbnailPath;
                    $this->items[] = $row;
                }

            }

        }else{

            $this->no = $this->arrayDefault($result, 'increment_id');
            $this->amount = $this->arrayDefault($result, 'total_paid');
            $this->status = $this->arrayDefault($result, 'status');
            $this->date = $this->arrayDefault($result, 'created_at');
            $payment = $this->arrayDefault($result, 'payment');
            $this->payment['method'] = $payment['method'];
            $this->shipping = [];
            $ship = $this->arrayDefault($result, 'extension_attributes');
            foreach ($this->arrayDefault($ship, 'shipping_assignments', []) as $shipping) {
                $shipping = $this->arrayDefault($shipping, 'shipping');
                $this->shipping['name'] = $shipping['address']['firstname'] . $shipping['address']['lastname'];
                $this->shipping['phone'] = $shipping['address']['telephone'];
                $this->shipping['postcode'] = $shipping['address']['postcode'];
                $this->shipping['address'] = $shipping['address']['city'].$shipping['address']['street'][0];

            }
            $this->shipping['shippingDescription'] = $this->arrayDefault($result, 'shipping_description');
            $this->shipping['shippingAmount'] = $this->arrayDefault($result, 'shipping_amount');
            $this->discount = $this->arrayDefault($result, 'discount_amount');
            $this->quantity = $this->arrayDefault($result, 'qty_ordered');


            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                if($this->arrayDefault($item, 'price') != 0) {
                    $row = [];
                    $row['source'] = ProjectConfig::MAGENTO;
                    $row['no'] = $this->arrayDefault($item, 'item_id');
                    $row['id'] = $this->arrayDefault($item, 'sku');
                    $row['name'] = $this->arrayDefault($item, 'name');
                    $row['spec'] = $this->arrayDefault($item, 'product_type');
                    $row['quantity'] = $this->arrayDefault($item, 'qty_ordered');
                    $row['price'] = $this->arrayDefault($item, 'price');
                    $row['description'] = $this->arrayDefault($result, 'shipping_description');
                    $ordered = $this->arrayDefault($item, 'qty_ordered');
                    $shipped = $this->arrayDefault($item, 'qty_shipped');
                    $refunded = $this->arrayDefault($item, '$qty_refunded');

                    if($shipped !== 0){
                        $row['status'] = $this->shippingStatus($this->arrayDefault($item, 'order_id'),$this->arrayDefault($item, 'sku'));
                    }else if($refunded != 0){
                        $row['status'] = '已退貨';
                    }else{
                        $row['status'] = '處理中';
                    }

                    $row['discount'] = $this->arrayDefault($result, 'discount_amount');
                    $generalPath = $product->find($this->arrayDefault($item, 'sku'))->imageUrls[0]['generalPath'];
                    $thumbnailPath = $product->find($this->arrayDefault($item, 'sku'))->imageUrls[0]['thumbnailPath'];
                    $row['imageUrls']['generalPath'] = $generalPath;
                    $row['imageUrls']['thumbnailPath'] = $thumbnailPath;

                    $this->items[] = $row;
                }
            }

        }

    }

    /**
     * 處理 cityPass 訂單資料建置
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

    /**
     * 處理物流狀態
     * @param $orderID
     * @param $sku
     * @return string
     */
    public function shippingStatus($orderID, $sku)
    {

            $order = new Order();
            $data= $order->getShippingInfo($orderID,$sku);
            if(!empty($data)) {
                $date = substr($data[0]['updated_at'], 0, 10);
                $shipinfo = $date . ' 出貨' . ' ' . $data[0]['title'] . ' ' . $data[0]['track_number'];
                return  $shipinfo;
            }else{
                return null;
            }



    }




}