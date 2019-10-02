<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarBookingDetail extends BaseModel
{
    
    protected $table = 'dining_car_booking_detail';

    protected $primaryKey = 'id';

    protected $connection = 'backend';

    protected $fillable = ['shop_id','name', 'phone', 'booking_number','booking_dayofweek','booking_date','booking_time','booking_people','status','editor','code'];

}
