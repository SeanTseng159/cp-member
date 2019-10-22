<?php

namespace App\Models;


use App\Models\Ticket\BaseModel;

class MenuOrderDetail extends BaseModel
{
    protected $table = 'menu_order_details';
    public $timestamps = true;

    public function __construct()
    {

    }
}
