<?php

namespace App\Models;


use App\Models\Ticket\BaseModel;
use App\Models\Ticket\DiningCar;
use App\Models\Ticket\Menu;
use App\Models\Ticket\Order;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuOrder extends BaseModel
{
    protected $table = 'menu_orders';
    protected $fillable = ['member_id', 'menu_order_no', 'pay_method', 'cellphone', 'date_time', 'note'];
    public $timestamps = true;
    use SoftDeletes;

    public function __construct()
    {

    }

    public function details()
    {
        return $this->hasMany(MenuOrderDetail::class, 'menu_order_id', 'id');
    }

    public function shop()
    {
        return $this->belongsTo(DiningCar::class, 'dining_car_id', 'id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'order_id', 'order_id');
    }
}
