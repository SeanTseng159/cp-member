<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class ShopWaitingRecord extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'dining_car_waiting_records';
    protected $connection = 'backend';
    protected $fillable = ['dining_car_id', 'waiting_no', 'member_id', 'date','time','name','cellphone','number','status'];





}
