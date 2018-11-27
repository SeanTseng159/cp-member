<?php
/**
 * User: lee
 * Date: 2018/11/22
 * Time: 上午 10:03
 */

namespace App\Result;

use App\Result\BaseResult;

class PaymentInfoResult extends BaseResult
{
    /**
     * 取得付款資料
     * @param $payments
     * @return array
     */
    public function getPayments($payments)
    {
        if ($payments->isEmpty()) return [];

        $result = [];
        foreach ($payments as $payment) {
            $result[] = $this->getPayment($payment);
        }

        return $result;
    }

    /**
     * 取得資料
     * @param $payment
     * @return object
     */
    private function getPayment($payment)
    {
        if (!$payment) return null;

        $pay = new \stdClass;
        $pay->id = $payment->sid;
        $pay->name = $payment->sname;
        $pay->type = $payment->name;

        return $pay;
    }

    /**
     * 取得取貨方式資料
     * @param $isPhysical
     * @return array
     */
    public function getShipments($isPhysical)
    {
        $shipment = new \stdClass;

        if ($isPhysical) {
            $shipment->id = 2;
            $shipment->name = '宅配到府';
            $shipment->description = '宅配到府';
            $shipment->type = 'delivery';
        }
        else {
            $shipment->id = 1;
            $shipment->name = '電子票券';
            $shipment->description = 'APP_我的票券';
            $shipment->type = 'virtual_ticket';
        }

        return [$shipment];
    }

    /**
     * 取得發票資訊
     * @return array
     */
    public function getBillings()
    {
        $billingType1 = new \stdClass;
        $billingType1->id = 1;
        $billingType1->name = '二聯式電子發票';
        $billingType1->type = 'default';

        $billingType2 = new \stdClass;
        $billingType2->id = 2;
        $billingType2->name = '三聯式電子發票';
        $billingType2->type = 'employer_id';

        return [
            $billingType1,
            $billingType2
        ];
    }
}