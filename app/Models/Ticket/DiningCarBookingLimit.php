<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;


class DiningCarBookingLimit extends BaseModel
{
    

    protected $table = 'dining_car_booking_limit';

    protected $primaryKey = 'id';


    /**
     * 取得店鋪資料
     */
    public function shopInfo()
    {
    	return $this->belongsTo('App\Models\Ticket\DiningCar', 'shop_id');
        //return $this->hasMany('App\Models\Ticket\DiningCar')->where('status', true);
    }
    /**
     * 取得封面圖
     */
    public function mainImg()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCarLogoImg','shop_id','dining_car_id');
    }
}
