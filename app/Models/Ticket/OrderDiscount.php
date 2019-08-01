<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class OrderDiscount extends BaseModel
{
    protected $table = 'order_discounts';
    protected $primaryKey = 'order_discount_id';
    public $timestamps = false;
}
