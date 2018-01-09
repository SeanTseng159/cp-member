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
use App\Models\TspgPostback;

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

 //       $checkout = new CheckoutResult();
 //       $checkout->cityPass($result);

        return ($result['statusCode'] === 200) ? $result['data'] : null;
    }

    /**
     * 結帳
     * @param $parameters
     * @return mixed
     */
    public function confirm($parameters)
    {
        $response = $this->putParameters($parameters)->request('POST', 'checkout/confirm');
        $result = json_decode($response->getBody(), true);

        Log::debug('===結帳===');
        Log::debug(print_r($result, true));

        return ($result['statusCode'] === 201) ? $result['data'] : false;
    }

    /**
     * 金融卡送金流(藍新)
     * @param $parameters
     * @return mixed
     */
    public function creditCard($parameters)
    {
        $parameter = $this->processPayment($parameters);
        $this->putParameters($parameter);

        $response = $this->request('POST', 'payment/credit_card');
        $result = json_decode($response->getBody(), true);

        Log::debug('===結帳信用卡(藍新)===');
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

    /**
     * 金融卡送金流(台新)
     * @param $parameters
     * @return mixed
     */
    public function transmit($parameters)
    {

        $parameter = [
          'order_no' => $parameters->orderNo
        ];

        /*$this->putParameters($parameter);
        $response = $this->request('POST', 'payment_tspg/credit_card');
        $result = json_decode($response->getBody(), true);*/

        $url = env('CITY_PASS_API_PATH') . 'payment_tspg/credit_card';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameter));
        $output = curl_exec($ch);
        curl_close($ch);

        var_dump($output);

        Log::debug('===Citypass結帳信用卡(台新)===');
        //Log::debug(print_r(json_decode($response->getBody(), true), true));
        Log::debug(print_r($output, true));
        $result = json_decode($output, true);

        if ($result) {
            $orderId = $result['data']['order_no'];
            $webpayOrderNo = $result['data']['webpay_order_no'];
            $url = $result['data']['result_url'];

            $data = [
                'order_id' => $orderId,
                'order_no' => $webpayOrderNo,
                'order_device' => $parameters->device,
                'order_source' => $parameters->source,
                'back_url' => md5($url)
            ];


            $pay = new TspgPostback();
            $pay->fill($data)->save();


            if ($result['statusCode'] === 200) ['id' => $orderId, 'url' => $url];
        }

        return ['id' => $parameters->orderNo, 'url' => ''];
    }

    /**
     * 更新訂單(台新結果回傳)
     * @param $parameters
     */
    public function updateOrder($parameters)
    {
        $this->putParameters($parameters);
        $response = $this->request('POST', 'payment_tspg/update_order');
        $result = json_decode($response->getBody(), true);

        Log::debug('===Citypass台新結果回傳更新訂單===');
        Log::debug(print_r(json_decode($response->getBody(), true), true));

    }

}
