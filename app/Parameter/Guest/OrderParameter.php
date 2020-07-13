<?php

namespace App\Parameter\Guest;

use App\Parameter\BaseParameter;

class OrderParameter extends BaseParameter
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function search()
    {
        $this->orderNo = $this->request->input('orderNo');
        $this->phoneNumber = $this->request->phoneNumber;

        return $this;
    }
}
