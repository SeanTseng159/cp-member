<?php

namespace App\Models;

use App\Models\Ticket\BaseModel;


use App\Traits\BackendSoftDeletes;

class OrderDiscount extends BaseModel
{
    use BackendSoftDeletes;

    protected $primaryKey = 'order_discount_id';

    public function order()
    {
        return $this->belongsTo('App\Models\Ticket\Order', 'order_no', 'order_no');
    }
}
