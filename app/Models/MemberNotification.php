<?php

namespace App\Models;

use App\Models\Ticket\DiningCar;
use Illuminate\Database\Eloquent\Model;

class MemberNotification extends Model
{
    protected $table = 'member_notification';
    protected $guarded = ['id'];
    protected $connection = 'backend';

    public function diningCar()
    {
        return $this->hasOne(DiningCar::class, 'id', 'dining_car_id');
//            ->where('status', 1);
    }

    /**
     * 取得封面圖
     */
    public function mainImg()
    {
        return $this->hasOne('App\Models\Ticket\DiningCarLogoImg', 'dining_car_id', 'dining_car_id');
    }
}
