<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 1:38
 */

namespace Ksd\Mediation\Magento;


use Ksd\Mediation\Helper\StringHelper;
use Ksd\Mediation\Result\Checkout\PaymentInfoResult;
use Ksd\Mediation\Result\Checkout\ShippingInfoResult;
use Ksd\Mediation\Result\CheckoutResult;

class Checkout extends Client
{
    use StringHelper;

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
        return [];
    }

    /**
     * 設定物流方式
     * @param $parameters
     */
    public function shipment($parameters)
    {
        $this->putShipping($parameters->shipment());
    }

    /**
     * 確認結帳方式
     * @param $parameters
     * @return array
     */
    public function confirm($parameters)
    {

        if($parameters->payment()->id === 'atm'){
            return $this->putPayment($parameters->payment());
        }else if($parameters->payment()->id === 'ipass_pay'){
            return $this->putPayment($parameters->payment());
        }else if($parameters->payment()->id === 'credit_card'){
            $response = $this->request('post', 'V1/ksd/mine/order');
            return trim($response->getBody(), '"');
        }else{
            return null;
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

        $this->request('POST', 'V1/carts/mine/shipping-information');
    }

    /**
     * 確認付款方式
     * @param $parameters
     * @return array
     */
    public function putPayment($parameters)
    {

        $parameter = $this->processPayment($parameters->payment(),$parameters->verify3d());
        $this->putParameters($parameter);
        $response = $this->request('POST', 'V1/carts/mine/payment-information');
        $body = $response->getBody();

        return [ 'id' => trim($body, '"')];
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
     * 處理付款資訊參數
     * @param $payment
     * @param $verify3d
     * @return array
     */
    private function processPayment($payment,$verify3d)
    {
        $parameter = [
            'paymentMethod' => [
                'method' => "checkmo",
 //               'method' => $payment->id,
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
}