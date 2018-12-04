<?php

namespace App\Parameter;

use App\Parameter\BaseParameter;
use App\Config\Ticket\OrderConfig;

class CheckoutParameter extends BaseParameter
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function buyNow()
    {
        $this->productId = $this->request->input('productId', 0);
        $this->specId = $this->request->input('specId', 0);
        $this->specPriceId = $this->request->input('specPriceId', 0);
        $this->quantity = $this->request->input('quantity', 0);

        return $this;
    }

    public function info()
    {
        return $this;
    }

    public function payment()
    {
        $this->deviceName = $this->request->input('device', 'web');
        $this->action = $this->request->input('action');
        $this->device = OrderConfig::PAYMENT_DEVICE[$this->deviceName];
        $this->payment = $this->request->input('payment');
        $this->shipment = $this->request->input('shipment');
        $this->billing = $this->request->input('billing');
        $this->hasLinePayApp = $this->request->input('hasLinePayApp', false);

        $paymentType = explode('_', $this->payment['id']);
        $this->payment['gateway'] = $paymentType[0];
        $this->payment['method'] = $paymentType[1];

        $this->shipment['address'] = ($this->shipment['id'] == 2) ? $this->shipment['zipcode'] . ' ' . $this->shipment['address'] : '';
        $this->shipment['phone'] = ($this->shipment['id'] == 2) ? $this->shipment['countryCode'] . ' ' . $this->shipment['cellphone'] : '';

        return $this;
    }

    public function repay()
    {
        $this->deviceName = $this->request->input('device', 'web');
        $this->device = OrderConfig::PAYMENT_DEVICE[$this->deviceName];
        $this->payment = $this->request->input('payment');
        $this->hasLinePayApp = $this->request->input('hasLinePayApp', false);

        $paymentType = explode('_', $this->payment['id']);
        $this->payment['gateway'] = $paymentType[0];
        $this->payment['method'] = $paymentType[1];

        return $this;
    }
}
