<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class ShopWaiting extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'dining_car_waiting_setting';
    protected $connection = 'backend';
    protected $fillable = ['dining_car_id', 'capacity', 'advance_notification', 'editor'];



}
