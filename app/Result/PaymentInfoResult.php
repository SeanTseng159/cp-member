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
    public function getPayments($payments, $source = null)
    {
        if ($payments->isEmpty()) return [];

        $result = [];
        foreach ($payments as $payment) {
            $result[] = $this->getPayment($payment, $source);
        }

        return $result;
    }

    /**
     * 取得資料
     * @param $payment
     * @return object
     */
    private function getPayment($payment, $source = null)
    {
        if (!$payment) return null;

        $pay = new \stdClass;

        if(!is_null($source)) $pay->source = $source;
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
    public function getShipments($isPhysical, $source = null, $shipments_type = 'object')
    {
        $shipment = new \stdClass;
        if(!is_null($source)) $shipment->source = $source;

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

        if($shipments_type == 'array') {
            $shipmentResult = [];
            $shipmentResult[] = $shipment;
            return $shipmentResult;
        }
        else {
            return $shipment;
        }
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
