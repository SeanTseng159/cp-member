<?php

namespace App\Models;

use App\Models\Ticket\BaseModel;

use App\Traits\BackendSoftDeletes;

class OrderDiscount extends BaseModel
{
    use BackendSoftDeletes;

    protected $primaryKey = 'order_discount_id';

}
