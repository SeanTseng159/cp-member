<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/8
 * Time: 上午 10:00
 */

namespace Ksd\Mediation\Parameter\Checkout;


use Ksd\Mediation\Helper\AddressHelper;
use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Parameter\BaseParameter;

class ConfirmParameter extends BaseParameter
{
    use ObjectHelper;
    use AddressHelper;

    private $source;
    private $isCheck;
    private $payment;
    private $shipment;
    private $billing;


    public function laravelRequest($request)
    {
        $this->source = $request->input('source');

        $this->processParameters($request, 'payment');
        $this->processParameters($request, 'shipment');
        $this->processParameters($request, 'billing');

        $this->shipment->userAddress = $this->address($this->shipment->userAddress);
    }

    public function processParameters($request, $property)
    {
        $paymentParameters = $request->input($property);
        $this->$property = new \stdClass();
        foreach ($paymentParameters as $key => $value) {
            $this->$property->$key = $value;
        }
    }

    public function checkSource($source = null)
    {
        $this->isCheck = $source === $this->source;
        return $this->isCheck;
    }

    public function payment($source = null)
    {
        if ($this->isCheck) {
            return $this->payment;
        }
        return null;
    }

    public function shipment($source = null)
    {
        if ($this->isCheck) {
            return $this->shipment;
        }
        return null;
    }

    public function billing($source = null)
    {
        if ($this->isCheck) {
            return $this->billing;
        }
        return null;
    }
}