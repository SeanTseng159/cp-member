<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 1:38
 */

namespace Ksd\Mediation\Magento;


use App\Exceptions\Api\Checkout\PayCreditCardFailException;
use App\Exceptions\Api\Checkout\ShipmentFailException;
use Ksd\Mediation\Helper\StringHelper;
use Ksd\Mediation\Result\Checkout\PaymentInfoResult;
use Ksd\Mediation\Result\Checkout\ShippingInfoResult;
use Ksd\Mediation\Result\CheckoutResult;
use GuzzleHttp\Exception\ClientException;
use Log;
use App\Models\TspgPostback;
use App\Models\TspgResultUrl;
use Ksd\Mediation\Magento\Order;
use Ksd\Mediation\Helper\EnvHelper;

class Checkout extends Client
{
    use StringHelper;
    use EnvHelper;

    private $cart;

    public function __construct()
    {
        $this->cart = new Cart();
        parent::__construct();
    }

    /**
     * 取得結帳資訊
     * @return CheckoutResult
     */
    public function info()
    {
        //刷新購物車，檢查商品是否下架
        $this->itemStatus();
        $checkout = new CheckoutResult();
        $checkout->magneto($this->paymentInfo(), $this->shippingInfo(), $this->billingInfo());
        return $checkout;
    }

    /**
     * 取得付款資訊
     * @return array
     */
    public function paymentInfo()
    {
        $response = $this->request('GET', 'V1/carts/mine/payment-information');
        $result = json_decode($response->getBody(), true);
        $data = [];
        if(array_key_exists('payment_methods', $result))
            foreach ($result['payment_methods'] as $row) {
                $paymentInfo = new PaymentInfoResult();
                $paymentInfo->magento($row);
                $data[] = $paymentInfo;
            }

        return $data;

    }

    /**
     * 取得配送資訊
     * @return array
     */
    public function shippingInfo()
    {
        $cart = $this->cart->detail();
        $data = [
            "address" => [
                "country_id"=>"TW"
            ]
        ];
        if (!empty($cart->id)) {
            $data['cart_id'] = $cart->id;
        } else {
            $data['cart_id'] = $this->cart->authorization($this->userToken)->createEmpty();
        }
        $this->putParameters($data);
        $response = $this->request('POST', 'V1/carts/mine/estimate-shipping-methods');
        $result = json_decode($response->getBody(), true);
        $data = [];
        foreach ($result as $row) {
            $shippingInfo = new ShippingInfoResult();
            $shippingInfo->magento($row);
            $data[] = $shippingInfo;
        }
        return $data;
    }

    /**
     * 取得帳單資訊
     * @return array
     */
    public function billingInfo()
    {
        $info = [
                [
                    "id" => 1,
                    "name" => "二聯式電子發票",
                    "type" => "default"
                ],
                [
                    "id" => 2,
                    "name" => "三聯式電子發票",
                    "type" => "employer_id"
                ]

        ];
        return $info;
    }

    /**
     * 設定物流方式
     * @param $parameters
     * @return bool
     */
    public function shipment($parameters)
    {

        $comment = $parameters->shipment()->remark;
        $this->putComment($comment);
        return $this->putShipping($parameters->shipment());
    }

    /**
     * 確認結帳方式
     * @param $parameters
     * @return array
     */
    public function confirm($parameters)
    {

        if($parameters->payment()->type === 'atm'){
            return $this->putPayment($parameters);
        }else if($parameters->payment()->type === 'ipass_pay'){
            return $this->putPayment($parameters);
        }else if($parameters->payment()->type === 'credit_card'){
            $id = date("YmdHis");
            return ['id' => date($id)];
        }else{
            return false;
        }

    }

    /**
     *信用卡送金流
     * @param $parameters
     * @return array
     */
    public function creditCard($parameters)
    {

            return $this->putPayment($parameters);

    }

    /**
     * 確認配送方式
     * @param $shipment
     * @return bool
     */
    public function putShipping($shipment)
    {
        $address = $this->processAddress($shipment);
        $methods = mb_split('_', $shipment->id);

        $this->putParameters([
            'addressInformation' => [
                'shipping_address' => $address,
                'billing_address' => $address,
                'shipping_method_code' => $methods[0],
                'shipping_carrier_code' => $methods[1],
            ],
            'shipping_method_code' => $methods[0],
            'shipping_carrier_code' => $methods[1],
        ]);
        try {
            $response = $this->request('POST', 'V1/carts/mine/shipping-information');
            $result = json_decode($response->getBody(), true);
        }catch (ClientException $e){
            throw new ShipmentFailException();
        }
        return isset($result['payment_methods']) ? true : false;

    }

    /**
     * 儲存備註
     * @param $parameter
     * @return bool
     */
    public function putComment($parameter)
    {
        if(!empty($parameter)) {
            $this->putParameters([
                "orderComment" => [
                    "comment" => $parameter,
                ]]);
            try {
                $response = $this->request('PUT', 'V1/carts/mine/set-order-comment');
                $result = json_decode($response->getBody(), true);
            }catch (ClientException $e){
                throw new ShipmentFailException();
            }
            return true;
        }else{
            return false;
        }

    }

    /**
     * 確認付款方式
     * @param $parameters
     * @return array
     */
    public function putPayment($parameters)
    {
        if($parameters->payment()->type==='credit_card') {
            $parameter = $this->processPayment($parameters->payment(), $parameters->verify3d());
        }else{
            $parameter = [
                'paymentMethod' => [
                     'method' => $parameters->payment()->id
                ]
            ];
        }
        $body = [];
        $this->putParameters($parameter);
        try {
            $response = $this->request('POST', 'V1/carts/mine/payment-information');
            $body = $response->getBody();
        }catch (ClientException $e){
            Log::debug('===magento非信用卡結帳(atm)===');
            Log::debug($e);
        }
        $orderId = !empty($body) ? (trim($body, '"')) : null;
        //如有三聯式發票資訊 抬頭&統編 則存入order/comment
        if(!empty($orderId)) {
            $admintoken = new Client();
            $this->authorization($admintoken->token);
            $billingInfo = [
                'invoiceTitle' => $parameters->billing()->invoiceTitle,
                'unifiedBusinessNo' => $parameters->billing()->unifiedBusinessNo
            ];
            $this->setInvoiceInfo($orderId, $billingInfo);

            //存入order/comment會有status的bug，須把狀態重新改為pending
            $order = new Order();
            $incrementId =  $order->orderIdToIncrementId(trim($body, '"'));
            $order->updateOrderState($orderId,$incrementId,'pending');
        }



        return empty($body) ? [] : [ 'id' => trim($body, '"')];
    }

    /**
     * 取得地址資訊
     * @param $shipment
     * @return array
     */
    private function processAddress($shipment)
    {
        $userNames = $this->str_split_unicode($shipment->userName);
        if(count($userNames) < 2) {
            // TODO:實作收件人名字不足兩個字處理
        }
        $firstName = array_shift($userNames);
        $lastName = join('', $userNames);
        return [
            'region' => $shipment->userAddress->area,
            'region_code' => $shipment->userAddress->area,
            'country_id' => 'TW',
            'street' => [
                $shipment->userAddress->street
            ],
            'telephone' => $shipment->userPhone,
            'postcode' => $shipment->userPostalCode,
            'city' => $shipment->userAddress->city,
            'firstname' => $firstName,
            'lastname' => $lastName
        ];
    }

    /**
     * 根據信用卡號取得信用卡類型
     * 判斷規則參考：https://zh.wikipedia.org/zh-tw/%E5%8F%91%E5%8D%A1%E8%A1%8C%E8%AF%86%E5%88%AB%E7%A0%81
     * @param $number
     * @return string
     */
    private function creditCardType($number)
    {
        $length = strlen($number);
        if (substr($number,0, 1) === '4' &&  ($length === 13 || $length === 19 )) {
            return 'VI';
        } else if($length === 16) {
            return $this->cardNumber16($number);
        }
    }

    /**
     * 信用卡16碼卡片類型判斷
     * @param $number
     * @return string
     */
    private function cardNumber16($number)
    {
        if (substr($number,0, 1) === '4') {
            return 'VI';
        } else if((
            intval(substr($number,0, 2)) >= 51 && intval(substr($number,0, 2)) <= 55) ||
            (intval(substr($number,0, 4)) >= 2221 && intval(substr($number,0, 4)) <= 2720) ) {
            return 'MC';
        } else if((intval(substr($number,0, 4)) >= 3528 && intval(substr($number,0, 4)) <= 3589)) {
            return 'JCB';
        }
    }

    /**
     * 處理信用卡付款資訊參數
     * @param $payment
     * @param $verify3d
     * @return array
     */
    private function processPayment($payment,$verify3d=null)
    {
        $parameter = [
            'paymentMethod' => [
 //               'method' => "checkmo",
                'method' => $payment->id,
            ]
        ];

        if (!empty($payment->creditCardNumber)) {
            $parameter['paymentMethod']['additional_data'] = [
                'cc_type' => $this->creditCardType($payment->creditCardNumber),
                'cc_exp_year' => $payment->creditCardYear,
                'cc_exp_month' => $payment->creditCardMonth,
                'cc_number' => $payment->creditCardNumber,
                'cc_cid' => $payment->creditCardCode,
                'eci' => $verify3d->eci,
                'cavv' => $verify3d->cavv,
                'xid' => $verify3d->xid,
            ];
        }

        return $parameter;
    }


    /**
     *信用卡送金流(台新)
     * @param $parameters
     * @return array
     */
    public function transmit($memberId, $parameters)
    {
        $parameter = [
            'paymentMethod' => [
                'method' => $parameters->payment()->id,
            ]
        ];

        if (!empty($parameters->payment()->creditCardNumber)) {
            $parameter['paymentMethod']['additional_data'] = [
                'cc_type' => $this->creditCardType($parameters->payment()->creditCardNumber),
                'cc_exp_year' => $parameters->payment()->creditCardYear,
                'cc_exp_month' => $parameters->payment()->creditCardMonth,
                'cc_number' => $parameters->payment()->creditCardNumber,
                'cc_cid' => $parameters->payment()->creditCardCode,
                'device' => $parameters->device,
                'source' => $parameters->source
            ];
        }

        $body = [];
        $this->putParameters($parameter);
        try {
            $response = $this->request('POST', 'V1/carts/mine/payment-information');
            $body = $response->getBody();

        }catch (ClientException $e){
            Log::debug('===magento結帳信用卡(台新)===');
            Log::debug($e);
            throw new PayCreditCardFailException();
        }

        $orderId = (!empty(trim($body, '"'))) ? trim($body, '"') : null ;

        //信用卡授權成功，訂單成立
        if(!empty($orderId)) {
            $admintoken = new Client();
            $this->authorization($admintoken->token);

            $path = sprintf('V1/orders/%s', $orderId);
            $response = $this->request('GET', $path);
            $result = json_decode($response->getBody(), true);

            $url = $result['payment']['additional_information'][4];
            $orderNo = $result['increment_id'];
            $device = $result['payment']['additional_information'][0];
            $source = $result['payment']['additional_information'][1];
            $order_No = $this->env('MAGENTO_ORDER_PREFIX').$orderNo;
            $data = [
                'member_id' => $memberId,
                'order_id' => $orderId,
                'order_no' => $order_No,
                'order_device' => $device,
                'order_source' => $source,
                'back_url' => md5($url)
            ];


            $pay = new TspgPostback();
            $pay->fill($data)->save();

            if(!empty($parameters->billing()->unifiedBusinessNo)) {
                //如有三聯式發票資訊 抬頭&統編 則存入order/comment
                $billingInfo = [
                    'invoiceTitle' => $parameters->billing()->invoiceTitle,
                    'unifiedBusinessNo' => $parameters->billing()->unifiedBusinessNo
                ];
                $this->setInvoiceInfo($orderId, $billingInfo);

                //存入order/comment會有status消失的bug，須把狀態重新改為pending
                $order = new Order();
                $incrementId = $order->orderIdToIncrementId($orderId);
                $order->updateOrderState($orderId, $incrementId, 'processing');
            }


            return [ 'id' => $orderId, 'url' => $url];

        }else{

            return [];
        }

    }

    /**
     * 更新訂單(台新結果回傳)
     * @param $data
     * @param $parameters
     */
    public function updateOrder($data,$parameters)
    {
        if(isset($data) && isset($parameters)) {
            $id = $data->order_id;
            $ret_code = $parameters->ret_code;
            $str = explode("_", $parameters->order_no);
            $incrementId = $str[1];
            //付款成功
            if ($ret_code === "00") {
                $parameter = [
                    'entity' => [
                        'entity_id' => $id,
                        'increment_id' => $incrementId,
                        'status' => 'processing',

                    ]
                ];
            } else {
                //3D驗證失敗，把訂單狀態改為canceled，並將原訂單重加回購物車
                $parameter = [
                    'entity' => [
                        'entity_id' => $id,
                        'increment_id' => $incrementId,
                        'status' => 'canceled',

                    ]
                ];
                $order = new Order();
                $order->getOrder($id);

            }

            $this->putParameters($parameter);
            $response = $this->request('PUT', 'V1/orders/create');
            $result = json_decode($response->getBody(), true);
            Log::debug('===magento台新結果回傳更新訂單===');
            Log::debug(print_r(json_decode($response->getBody(), true), true));
        }

    }

    /**
     * 處理台新回傳result_url
     * @param $parameters
     * @return mixed
     */
    public function resultUrl($parameters)
    {

        $data = [
            'ret_code' => $parameters->ret_code,
            'ret_msg' => $parameters->ret_msg,
            'order_no' => $parameters->order_no,
            'auth_id_resp' => $parameters->auth_id_resp,
            'rrn' => $parameters->rrn,
            'order_status' => $parameters->order_status,
            'auth_type' => $parameters->auth_type,
            'cur' => $parameters->cur,
            'purchase_date' => $parameters->purchase_date,
            'tx_amt' => $parameters->tx_amt,
            'settle_amt' => $parameters->settle_amt,
            'settle_seq' => $parameters->settle_seq,
            'settle_date' => $parameters->settle_date,
            'refund_trans_amt' => $parameters->refund_trans_amt,
            'refund_rrn' => $parameters->refund_rrn,
            'refund_auth_id_resp' => $parameters->refund_auth_id_resp,
            'refund_date' => $parameters->refund_date,

        ];


        $pay = new TspgResultUrl();
        $pay->fill($data)->save();


        return true;
    }

    /**
     * 建立隨機ID
     * @param $key
     * @return string
     */
    private function genCacheKey($key)
    {
        $date = new \DateTime();
        return sprintf($key,$date->format('Ymd'));
    }


    /**
     * 儲存發票資訊
     * @param $orderId
     * @param $parameter
     * @return string
     */
    private function setInvoiceInfo($orderId,$parameter=null)
    {
        if(isset($orderId) && isset($parameter)) {
            $parameter = [
                'statusHistory' => [
                    "comment" => implode('&', $parameter)
                ]
            ];
            $this->putParameters($parameter);
            $this->request('POST', 'V1/orders/' . $orderId . '/comments');
            return true;

        }else{
            return true;
        }
    }

    /**
     * 商品狀態判斷
     * @return bool
     */
    public function itemStatus()
    {
        $result = [];
        try {

            $response = $this->request('GET', 'V1/carts/mine');
            $result = json_decode($response->getBody(), true);


        } catch (ClientException $e) {
            // TODO:處理抓取不到購物車資料
        }
        if(empty($result['items'])){
            return true;
        }else{
            return false;
        }

    }



}