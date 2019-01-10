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
