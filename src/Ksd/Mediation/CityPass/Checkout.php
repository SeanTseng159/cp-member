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
        $response = $this->request('GET', 'checkout/confirm');
        $result = json_decode($response->getBody(),true);

        return $result;
    }

}