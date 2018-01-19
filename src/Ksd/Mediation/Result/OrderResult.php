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
            $this->id = $this->arrayDefault($result, 'entity_id');
            $this->orderNo = $this->arrayDefault($result, 'increment_id');
            $this->orderAmount = $this->arrayDefault($result, 'subtotal') + $this->arrayDefault($result, 'shipping_amount');
            $this->orderDiscountAmount = $this->arrayDefault($result, 'discount_amount');
            $this->orderStatus = $this->getStatus(ProjectConfig::MAGENTO,$this->arrayDefault($result, 'status'));
            $this->orderStatusCode = $this->getStatusCode(ProjectConfig::MAGENTO,$this->arrayDefault($result, 'status'));
            $this->orderDate = date('Y-m-d H:i:s', strtotime('+8 hours', strtotime($this->arrayDefault($result, 'created_at'))));
            $payment = $this->arrayDefault($result, 'payment');
            $comment = $this->arrayDefault($result, 'status_histories');
            $this->payment = $this->putMagentoPayment($payment);
//            $this->payment['username'] =   $this->arrayDefault($result, 'customer_firstname') . $this->arrayDefault($result, 'customer_lastname');
            $this->shipping = [];
            $ship = $this->arrayDefault($result, 'extension_attributes');
            foreach ($this->arrayDefault($ship, 'shipping_assignments', []) as $shipping) {
                $shipping = $this->arrayDefault($shipping, 'shipping');
                $this->shipping['name'] = $shipping['address']['firstname'] . $shipping['address']['lastname'];
                $this->shipping['phone'] = $shipping['address']['telephone'];
                $this->shipping['code'] = $shipping['address']['postcode'];
                $this->shipping['address'] = $shipping['address']['city'].$shipping['address']['street'][0];

            }
            $this->shipping['description'] = $this->arrayDefault($result, 'shipping_description');
            $this->shipping['amount'] = $this->arrayDefault($result, 'shipping_amount');

            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                if($this->arrayDefault($item, 'price') != 0) {
                    $row = [];
                    $name = $this->arrayDefault($item, 'name');
                    $nameSplit = mb_split('-', $name, 2);
                    $row['source'] = ProjectConfig::MAGENTO;
//                    $row['no'] = $this->arrayDefault($item, 'item_id');
                    $row['itemId'] = $this->arrayDefault($item, 'sku');
                    $row['name'] = $nameSplit[0];
                    $row['spec'] = isset($nameSplit[1]) ? $nameSplit[1] : '';
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
                    $row['imageUrl'] = null;
                    /*
                    $path = $product->findItemImage($this->arrayDefault($item, 'sku'));
                    $row['imageUrl'] = $this->magentoImageUrl($path['file']);
                    */

//                    $row['imageUrl'] = $this->arrayDefault($item, 'extension_attributes', '')['image_url'];


                    $this->items[] = $row;
                }

            }

        }else{

            $this->orderNo = $this->arrayDefault($result, 'increment_id');
            $this->orderAmount = $this->arrayDefault($result, 'grand_total');
            $this->orderItemAmount = $this->arrayDefault($result, 'subtotal');
            $this->orderDiscount = $this->arrayDefault($result, 'discount_amount');
            $this->orderStatus = $this->getStatus(ProjectConfig::MAGENTO,$this->arrayDefault($result, 'status'));
            $this->orderStatusCode = $this->getStatusCode(ProjectConfig::MAGENTO,$this->arrayDefault($result, 'status'));
            $this->orderDate = date('Y-m-d H:i:s', strtotime('+8 hours', strtotime($this->arrayDefault($result, 'created_at'))));
            $payment = $this->arrayDefault($result, 'payment');
            $comment = $this->arrayDefault($result, 'status_histories');
            $this->payment = $this->putMagentoPayment($payment,$comment,$this->arrayDefault($result, 'entity_id'),$this->arrayDefault($result, 'increment_id'));
            $this->shipping = [];
            $ship = $this->arrayDefault($result, 'extension_attributes');
            foreach ($this->arrayDefault($ship, 'shipping_assignments', []) as $shipping) {
                $shipping = $this->arrayDefault($shipping, 'shipping');
                $this->shipping['name'] = $shipping['address']['firstname'] . $shipping['address']['lastname'];
                $this->shipping['phone'] = $shipping['address']['telephone'];
                $this->shipping['code'] = $shipping['address']['postcode'];
                $this->shipping['address'] = $shipping['address']['city'].$shipping['address']['street'][0];

            }
            $this->shipping['description'] = $this->arrayDefault($result, 'shipping_description');
            $this->shipping['amount'] = $this->arrayDefault($result, 'shipping_amount');
            $this->shipping['status'] = $this->shippingStatus($this->arrayDefault($result, 'entity_id'));
            $this->shipping['traceCode'] = $this->shippingStatus($this->arrayDefault($result,'entity_id'),true);
            $this->quantity = $this->arrayDefault($result, 'qty_ordered');


            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                if($this->arrayDefault($item, 'price') != 0) {
                    $row = [];
                    $row['source'] = ProjectConfig::MAGENTO;
                    $row['no'] = $this->arrayDefault($item, 'item_id');
                    $row['id'] = $this->arrayDefault($item, 'sku');
                    $name = $this->arrayDefault($item, 'name');
                    $nameSplit = mb_split('-', $name, 2);
                    $row['name'] = $nameSplit[0];
                    $row['spec'] = isset($nameSplit[1]) ? $nameSplit[1] : '';
                    $row['quantity'] = $this->arrayDefault($item, 'qty_ordered');
                    $row['price'] = $this->arrayDefault($item, 'price');
                    $row['description'] = $this->arrayDefault($result, 'shipping_description');
                    $ordered = $this->arrayDefault($item, 'qty_ordered');
                    $shipped = $this->arrayDefault($item, 'qty_shipped');
                    $refunded = $this->arrayDefault($item, '$qty_refunded');

                    if($shipped !== 0){
                        $row['status'] = $this->shippingStatus($this->arrayDefault($item, 'order_id'),$this->arrayDefault($item, 'sku'));
                        $row['statusCode'] = '01';
                    }else if($refunded != 0){
                        $row['status'] = '已退貨';
                        $row['statusCode'] = '04';
                    }else{
                        $row['status'] = '處理中';
                        $row['statusCode'] = '01';
                    }

                    $row['discount'] = $this->arrayDefault($result, 'discount_amount');
/*
                    $path = $product->findItemImage($this->arrayDefault($item, 'sku'));
                    $row['imageUrl'] = $this->magentoImageUrl($path['file']);
*/
                    $row['imageUrl'] = $this->arrayDefault($item, 'extension_attributes', '')['image_url'];
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

        if (!$isDetail) {
            $this->orderNo = $this->arrayDefault($result, 'orderNo');
            $this->orderAmount = $this->arrayDefault($result, 'orderAmount');
            $this->orderDiscountAmount = $this->arrayDefault($result, 'orderDiscountAmount');
            $this->orderStatus = $this->getStatus(ProjectConfig::CITY_PASS,$this->arrayDefault($result, 'orderStatus'), $this->arrayDefault($result, 'isRePayment'));
            $this->orderStatusCode = $this->getStatusCode(ProjectConfig::CITY_PASS,$this->arrayDefault($result, 'orderStatus'), $this->arrayDefault($result, 'isRePayment'));
            $this->isRePayment = $this->arrayDefault($result, 'isRePayment');
            $this->orderDate = $this->arrayDefault($result, 'orderDate');
            $this->payment = $this->arrayDefault($result, 'payment');
            $this->shipping = $this->arrayDefault($result, 'shipping');


            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                $row = [];
                $row['source'] = ProjectConfig::CITY_PASS;
                $row['itemId'] = $this->arrayDefault($item, 'itemId');
                $row['no'] = $this->arrayDefault($item, 'no');
                $row['name'] = $this->arrayDefault($item, 'name');
                $row['spec'] = $this->arrayDefault($item, 'spec');
                $row['quantity'] = $this->arrayDefault($item, 'quantity');
                $row['price'] = $this->arrayDefault($item, 'price');
                $row['description'] = $this->arrayDefault($item, 'description');
                $row['statusCode'] = $this->arrayDefault($item, 'statusCode');
                $row['status'] = $this->arrayDefault($item, 'status');
                $row['discount'] = $this->arrayDefault($item, 'discount');
                $row['imageUrl'] = $this->arrayDefault($item, 'imageUrl');

                $this->items[] = $row;

            }
        } else {

            $this->orderNo = $this->arrayDefault($result, 'orderNo');
            $this->orderAmount = $this->arrayDefault($result, 'orderAmount');
            $this->orderItemAmount = $this->arrayDefault($result, 'orderItemAmount');
            $this->orderDiscount = $this->arrayDefault($result, 'orderDiscount');
            $this->orderStatus = $this->getStatus(ProjectConfig::CITY_PASS, $this->arrayDefault($result, 'orderStatus'));
            $this->orderStatusCode = $this->getStatusCode(ProjectConfig::CITY_PASS, $this->arrayDefault($result, 'orderStatus'), $this->arrayDefault($result, 'isRePayment'));
            $this->orderDate = $this->arrayDefault($result, 'orderDate');
            $this->payment = $this->arrayDefault($result, 'payment');
            $this->shipment = $this->arrayDefault($result, 'shipment');
            $this->items = [];
            foreach ($this->arrayDefault($result, 'items', []) as $item) {
                $row = [];
                $row['source'] = ProjectConfig::CITY_PASS;
                $row['itemId'] = $this->arrayDefault($item, 'itemId');
                $row['no'] = $this->arrayDefault($item, 'no');
                $row['name'] = $this->arrayDefault($item, 'name');
                $row['spec'] = $this->arrayDefault($item, 'spec');
                $row['quantity'] = $this->arrayDefault($item, 'quantity');
                $row['price'] = $this->arrayDefault($item, 'price');
                $row['description'] = $this->arrayDefault($item, 'description');
                $row['statusCode'] = $this->arrayDefault($item, 'statusCode');
                $row['status'] = $this->arrayDefault($item, 'status');
                $row['discount'] = $this->arrayDefault($item, 'discount');
                $row['imageUrl'] = $this->arrayDefault($item, 'imageUrl');

                $this->items[] = $row;

            }

        }
    }
    /**
     * 處理物流狀態
     * @param $orderID
     * @param $code
     * @return string
     */
    public function shippingStatus($orderID,$code=false)
    {

            $order = new Order();
            $data= $order->getShippingInfo($orderID);
            if(!empty($data)) {
                $date = substr($data[0]['updated_at'], 0, 10);
                $shipinfo = $date . ' 出貨';
                $shipcode = $data[0]['tracks'][0]['title'] . ' ' . $data[0]['tracks'][0]['track_number'];
                if(!$code) {
                    return $shipinfo;
                }else{
                    return $shipcode;

                }
            }else{
                return null;
            }



    }

    /**
     * 狀態轉換
     * @param $source
     * @param $key
     * @return string
     */
    public function getStatus($source, $key, $isRePayment = false)
    {

        if ($source ==='magento') {
            switch ($key) {
                case 'pending': # 待付款
                    return "待付款";
                    break;
                case 'complete': # 訂單完成(已出貨)
                    return "已完成";
                    break;
                case 'holded': # 退貨處理中
                    return "退貨處理中";
                    break;
                case 'canceled': # 已退貨
                    return "已退貨";
                    break;
                case 'processing': # 付款成功(前台顯示已完成)，尚未出貨
                    return "已完成";
                    break;
            }
        } else if($source ==='ct_pass'){
            switch ($key) {

                case '00': # 重新付款 || 待付款
                    return ($isRePayment) ? "重新付款" : "待付款";
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
        }else{
                return null;

        }

    }

    /**
     * magento狀態代碼轉換
     * @param $key
     * @return string
     */
    public function getStatusCode($source, $key, $isRePayment = false)
    {
        if ($source ==='magento') {
            switch ($key) {

                case 'pending': # 待付款
                    return "00";
                    break;
                case 'complete': # 已完成(完成出貨)
                    return "01";
                    break;
                case 'holded': # 退貨處理中
                    return "04";
                    break;
                case 'canceled': # 已退貨
                    return "03";
                    break;
                case 'processing': # 已完成(完成付款)
                    return "01";
                    break;
            }
        } else if($source ==='ct_pass'){
            # 重新付款
            if ($key === '00' && $isRePayment) return '07';
            else return $key;
        }
        else {
            return null;
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
     * @param $comment
     * @param $id
     * @return array
     */
    private function putMagentoPayment($payment , $comment=null, $id=null, $incrementId=null)
    {

        $result = [];
        $method = $payment['method'];
        $additionalInformation = $payment['additional_information'];

        if ($method === 'tspg_atm') {

            $result['gateway'] = "tspg";
            $result['method'] = "atm";
            $result = [
                'bankId' => $this->arrayDefault($additionalInformation, 1),
                'virtualAccount' => $this->arrayDefault($additionalInformation, 2),
                'amount' => $this->arrayDefault($additionalInformation, 3),
                'paymentPeriod' => $this->arrayDefault($additionalInformation, 4),
                'gateway' => "tspg",
                'method' => "atm",
                'title' => "ATM虛擬帳號"
            ];
        }

        if ($method === 'tspg_transmit') {

            $result['gateway'] = "tspg";
            $result['method'] = "credit_card";
            $result['title'] = $this->paymentTypeTrans(isset($additionalInformation[2]) ? $additionalInformation[2] : $additionalInformation[0]);
        }

        if($method === 'ipasspay'){
            $data = !empty($comment) ? explode("&",$comment[0]['comment']) : null;
            if(!empty($comment)) {
                $result['gateway'] = "ipasspay";
                $result['title'] = $this->paymentTypeTrans($additionalInformation[0], $data);
                $result['method'] = $this->getPaymentMethod(isset($data[4]) ? $data[4] : null);
            }else{
                //comment沒資料，表示沒接受到ipassPay回饋訊息即離開付款，把此筆訂單取消，並將商品加回購物車
                $order = new Order();
                $order->getOrder($id);
                $order->updateOrderState($id,$incrementId,"canceled");
                $this->orderStatus = "付款失敗";
                $this->orderStatusCode = "03";
                $result['gateway'] = "";
                $result['title'] = "";
                $result['method'] = "";

            }
        }
        return $result;
    }

    /**
     * 付款方式名稱轉換
     * @param $key
     * @param $data
     * @return string
     */
    public function paymentTypeTrans($key,$data=null)
    {

        if(!empty($key)) {
            if ($key === "Neweb Atm Payment") {
                return "ATM虛擬帳號";
            } else if ($key === "Neweb Api Payment") {
                return "信用卡一次付清";
            } else if ($key === "Tspg Atm Payment") {
                return "ATM虛擬帳號";
            } else if ($key === "Tspg Api Payment") {
                return "信用卡一次付清";
            } else if ($key === "Check / Money order") {
                return "測試用";
            } else if ($key === "Ipass Pay") {
                if (isset($data[4])) {
                    if ($data[4] === "ACCLINK") {
                        return "IpassPay(約定帳戶付款)";
                    } else if ($data[4] === "CREDIT") {
                        return "IpassPay(信用卡付款)";
                    } else if ($data[4] === "VACC") {
                        return "IpassPay(ATM轉帳付款)";
                    } else if ($data[4] === "WEBATM") {
                        return "IpassPay(網路銀行轉帳付款)";
                    } else if ($data[4] === "BARCODE") {
                        return "IpassPay(超商條碼繳費)";
                    } else if ($data[4] === "ECAC") {
                        return "IpassPay(電子支付帳戶付款)";
                    } else {
                        return "IpassPay";
                    }
                } else {
                    return "IpassPay";
                }
            }else{
                return  "取不到付款資料";
            }
        }else{
            return  "取不到付款資料";
        }


    }

    /**
     * 訂單明細商品狀態轉換
     * @param $source
     * @param $key
     * @return string
     */
    public function getItemStatus($source, $key)
    {
        if ($source ==='magento') {
            switch ($key) {

                case 'pending': # 待付款
                    return "待付款";
                    break;
                case 'complete': # 訂單完成(已出貨且開立發票)
                    return "已完成";
                    break;
                case 'holded': # 退貨處理中
                    return "退貨處理中";
                    break;
                case 'canceled': # 已退貨
                    return "已退貨";
                    break;
                case 'processing': # 付款成功(前台顯示已完成)，尚未出貨
                    return "已完成";
                    break;
            }
        } else if($source ==='ct_pass'){
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
        }else{
            return null;

        }

    }

    /**
     * 訂單明細商品使用狀態轉換
     * @param $source
     * @param $key
     * @return string
     */
    public function getItemUseStatus($source, $key)
    {
        if ($source ==='magento') {
            switch ($key) {

                case '00': # 保留中
                    return "保留中";
                    break;
                case '01': # 處理中
                    return "處理中";
                    break;
                case '02': # 已送達
                    return "已送達";
                    break;
                case '03': # 退貨中
                    return "退貨中";
                    break;
                case '04': # 已退貨
                    return "已退貨";
                    break;
                case '05': # 已轉贈
                    return "已轉贈";
                    break;
            }
        } else if($source ==='ct_pass'){
            switch ($key) {

                case '00': # 保留中
                    return "保留中";
                    break;
                case '01': # 未使用
                    return "未使用";
                    break;
                case '02': # 已使用
                    return "已使用";
                    break;
                case '03': # 退貨中
                    return "退貨中";
                    break;
                case '04': # 已退貨
                    return "已退貨";
                    break;
                case '05': # 已轉贈
                    return "已轉贈";
                    break;
            }
        }else{
            return null;

        }

    }

    /**
     * magento ipassPay狀態轉換
     * @param $key
     * @return string
     */
    public function getIpassPayStatus($key)
    {
        switch ($key) {

            case 'pending': # 待付款
                return "00";
                break;
            case 'complete': # 已完成
                return "01";
                break;
            case 'holded': # 退貨處理中
                return "04";
                break;
            case 'canceled': # 已退貨
                return "03";
                break;
            case 'processing': # 處理中
                return "01";
                break;
        }

    }


    /**
     * 訂單明細ipasspay付款方式轉換
     * @param $key
     * @return string
     */
    public function getPaymentMethod($key)
    {
            switch ($key) {

                case 'ACCLINK': # 信用卡
                    return "acclink";
                    break;
                case 'CREDIT': #　ATM
                    return "credit_card";
                    break;
                case 'WEBATM': # iPassPay
                    return "atm";
                    break;
                case 'BARCODE': # iPassPay
                    return "barcode";
                    break;
                case 'ECAC': # iPassPay
                    return "ecac";
                    break;
                case 'VACC': # iPassPay
                    return "atm";
                    break;
                case null: # iPassPay
                    return "iPassPay";
                    break;

            }


    }

}
