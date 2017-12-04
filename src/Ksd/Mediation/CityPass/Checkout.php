<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/16
 * Time: 上午 9:30
 */

namespace Ksd\Mediation\CityPass;


use Ksd\Mediation\Result\CheckoutResult;
use Log;

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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://139.162.122.115/backend-citypass/api/checkout/confirm');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->putParameters($parameters)->getParameters()));
        $output = curl_exec($ch);
        curl_close($ch);

        //$response = $this->putParameters($parameters)->request('POST', 'checkout/confirm');
        //$result = json_decode($response->getBody(), true);

        Log::debug('===結帳===');
        Log::debug(print_r($this->putParameters($parameters)->getParameters(), true));
        Log::debug(print_r($output, true));

        //return ($result['statusCode'] === 201) ? $result['data'] : false;
        return false;
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
        $response = $this->request('POST', 'payment/credit_card');
        $result = json_decode($response->getBody(), true);

        Log::debug('===結帳信用卡===');
        Log::debug(print_r(json_decode($response->getBody(), true), true));

        return ($result['statusCode'] === 200);
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
                'error_code' => $payment->verify3d()->errorCode,
                'error_message' => $payment->verify3d()->errorMessage,
            ]
        ];
        return $parameter;
    }

}
