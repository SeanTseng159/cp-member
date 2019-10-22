<?php

namespace App\Models;


use App\Models\Ticket\BaseModel;
use App\Models\Ticket\Menu;

class MenuOrderDetail extends BaseModel
{
    protected $table = 'menu_order_details';
    public $timestamps = true;

    public function __construct()
    {

    }

    public function menu()
    {
        return $this->hasOne(Menu::class, 'id', 'menu_id');
    }
}
