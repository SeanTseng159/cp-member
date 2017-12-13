<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/16
 * Time: 上午 11:06
 */

namespace Ksd\Mediation\Result;


class CheckoutResult
{
    /**
     * 處理 magento 結帳資訊
     * @param $payments
     * @param $shipments
     * @param $billings
     */
    public function magneto($payments, $shipments, $billings)
    {
        $this->payments = $payments;
        $this->shipments = $shipments;
        $this->billings = $billings;
    }

    /**
     * 處理 city pass 結帳資訊
     * @param $result
     */
    public function cityPass($result)
    {
        if ((is_array($result) || is_object(is_array($result))) && count($result) > 0) {
            foreach ($result as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}