<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarBookingDetail extends BaseModel
{
    
    protected $table = 'dining_car_booking_detail';

    protected $primaryKey = 'id';

    protected $connection = 'backend';

    protected $fillable = ['shop_id','member_id','name', 'phone', 'booking_number','booking_dayofweek','booking_date','booking_time','booking_people','status','editor','demand','code'];

    /**
     * 取得店鋪限制
     */
    public function shopLimit()
    {
    	return $this->belongsTo('App\Models\Ticket\DiningCarBookingLimit', 'shop_id','shop_id');
        //return $this->hasMany('App\Models\Ticket\DiningCar')->where('status', true);
    }

    
    /**
     * 取得店鋪相關所有資訊
     */
    public function diningCar()
    {
    	return $this->belongsTo('App\Models\Ticket\DiningCar', 'shop_id','id');
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
