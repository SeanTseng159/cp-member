<?php

namespace App\Models;

use App\Models\Ticket\DiningCar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopWaitingRecord extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'dining_car_waiting_records';
    protected $connection = 'backend';
    protected $fillable = ['dining_car_id', 'waiting_no', 'member_id', 'date', 'time', 'name', 'cellphone', 'number', 'status'];


    public function shop()
    {
        return $this->belongsTo(DiningCar::class,'dining_car_id','id');
    }


}
