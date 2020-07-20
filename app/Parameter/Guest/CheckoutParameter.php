<?php

namespace App\Parameter\Guest;

use App\Parameter\BaseParameter;

class CheckoutParameter extends BaseParameter
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function info()
    {
        $this->token = $this->request->token;
        $this->supplierId = $this->request->input('supplierId');
        $this->products = $this->request->input('products');

        return $this;
    }
}
