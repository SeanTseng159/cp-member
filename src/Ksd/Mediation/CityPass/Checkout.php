<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/16
 * Time: 上午 9:30
 */

namespace Ksd\Mediation\CityPass;


use Ksd\Mediation\Result\CheckoutResult;

class Checkout extends Client
{

    /**
     * 取得付款資訊
     * @return CheckoutResult
     */
    public function info()
    {
        $response = $this->request('GET', 'checkout/info');
        $result = json_decode($response->getBody(),true);

        $checkout = new CheckoutResult();
        $checkout->cityPass($result);

        return $checkout;
    }

    /**
     * 結帳
     * @param $parameters
     * @return mixed
     */
    public function confirm($parameters)
    {
        $this->putParameters($parameters);
        $response = $this->request('POST', 'checkout/confirm');
        $result = json_decode($response->getBody(),true);

        return $result;
    }

    /**
     * 金融卡送金流
     * @param $parameters
     * @return mixed
     */
    public function creditCard($parameters)
    {
        $parameter = $this->processPayment($parameters);
        $this->putParameters($parameter);
        $response = $this->request('POST', 'V1/carts/mine/payment-information');
        $body = $response->getBody();
        $result = json_decode($body, true);

        return $result;
    }

    /**
     * 處理資訊參數
     * @param $payment
     * @return array
     */
    private function processPayment($payment)
    {
        $parameter = [
            'order_no' => $payment->orderNo,
            '_3d_response' => [
                'eci' => $payment->verify3d()->eci,
                'cavv' => $payment->verify3d()->cavv,
                'xid' => $payment->verify3d()->xid,
                'errorCode' => $payment->verify3d()->errorCode,
                'errorMessage' => $payment->verify3d()->errorMessage,
            ]
        ];
        return $parameter;
    }

}