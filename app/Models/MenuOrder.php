<?php

namespace App\Models;


use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuOrder extends BaseModel
{
    protected $table = 'menu_orders';
    protected $fillable = ['member_id','menu_order_no','pay_method','cellphone','date_time','note'];
    public $timestamps = true;
    use SoftDeletes;

    public function __construct()
    {

    }
}
