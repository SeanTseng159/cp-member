<?php

namespace App\Parameter;

use App\Parameter\BaseParameter;

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
}
