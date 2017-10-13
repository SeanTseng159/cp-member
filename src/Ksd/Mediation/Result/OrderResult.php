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
use Ksd\Mediation\Helper\EnvHelper;

class OrderResult
{
    use ObjectHelper;
    use EnvHelper;


    /**
     * 處理 magento 訂單資料建置
     * @param $result
     * @param bool $isDetail
     */
    public function magento($result, $isDetail = false)
    {
        $this->source = ProjectConfig::MAGENTO;
        $product = new Order();

        if(!$isDetail) {
            $this->orderNo = $this->arrayDefault($result, 'increment_id');
            $this->orderAmount = $this->arrayDefault($result, 'total_paid');
            $this->orderStatus = $this->getStatus(ProjectConfig::MAGENTO,$this->arrayDefault($result, 'status'));
            $this->orderDate = $this->arrayDefault($result, 'created_at');
            $payment = $this->arrayDefault($result, 'payment');
            $this->payment = $this->putMagentoPayment($payment);
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
                    $path = $product->findItemImage($this->arrayDefault($item, 'sku'));
                    $generalPath = $this->magentoImageUrl($path['file']);
                    $thumbnailPath = in_array('thumbnail', $path['types']) ? $this->magentoImageUrl($path['file'] ): '';
                    $row['imageUrls']['generalPath'] = $generalPath;
                    $row['imageUrls']['thumbnailPath'] = $thumbnailPath;
                    $this->items[] = $row;
                }

            }

        }else{

            $this->no = $this->arrayDefault($result, 'increment_id');
            $this->amount = $this->arrayDefault($result, 'total_paid');
            $this->status = $this->getStatus(ProjectConfig::MAGENTO,$this->arrayDefault($result, 'status'));
            $this->date = $this->arrayDefault($result, 'created_at');
            $payment = $this->arrayDefault($result, 'payment');
            $this->payment = $this->putMagentoPayment($payment);
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
                    $path = $product->findItemImage($this->arrayDefault($item, 'sku'));
                    $generalPath = $this->magentoImageUrl($path['file']);
                    $thumbnailPath = in_array('thumbnail', $path['types']) ? $this->magentoImageUrl($path['file'] ): '';
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
            $this->orderStatus = $this->getStatus(ProjectConfig::CITY_PASS,$this->arrayDefault($result, 'orderStatus'));
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
            $this->status = $this->getStatus(ProjectConfig::CITY_PASS,$this->arrayDefault($result, 'orderStatus'));
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

    /**
     * 狀態轉換
     * @return string
     */
    public function getStatus($source, $key)
    {
        if ($source . equalTo('magento')) {
            switch ($key) {

                case 'pending': # 待付款
                    return "待付款";
                    break;
                case 'complete': # 已完成
                    return "已完成";
                    break;
                case 'holded': # 部分退貨
                    return "部分退貨";
                    break;
                case 'cancel': # 已退貨
                    return "已退貨";
                    break;
                case 'processing': # 處理中
                    return "處理中";
                    break;
            }
        } else {
            switch ($key) {

                case '00': # 待付款
                    return "待付款";
                    break;
                case '01': # 已完成
                    return "已完成";
                    break;
                case '02': # 部分退貨
                    return "部分退貨";
                    break;
                case '03': # 已退貨
                    return "已退貨";
                    break;
                case '04': # 處理中
                    return "處理中";
                    break;
            }
        }

    }

    /**
     * 取得 magento 圖片對應路徑
     * @param $path
     * @return string
     */
    private function magentoImageUrl($path)
    {
        $basePath = $this->env('MAGENTO_PRODUCT_PATH');
        return $basePath . $path;
    }

    /**
     * 設定付款資訊
     * @param $payment
     * @return array
     */
    private function putMagentoPayment($payment)
    {
        $result = [];
        $method = $payment['method'];
        $additionalInformation = $payment['additional_information'];
        if ($method === 'neweb_atm') {
            $result = [
                'bankId' => $additionalInformation[1],
                'virtualAccount' => $additionalInformation[2],
                'amount' => $additionalInformation[3]
            ];
        }
        $result['method'] = $method;
        $result['title'] = $additionalInformation[0];
        return $result;
    }
}