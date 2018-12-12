<?php

namespace App\Parameter;

use App\Parameter\BaseParameter;
use App\Config\Ticket\OrderConfig;

class CartParameter extends BaseParameter
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
        $this->additionalProducts = $this->request->input('additionalProducts');

        return $this;
    }

    public function info()
    {
        $this->action = $this->request->input('action');

        return $this;
    }

    public function market()
    {
        $this->marketId = $this->request->input('marketId', 0);
        $this->products = $this->request->input('products');

        return $this;
    }
}
