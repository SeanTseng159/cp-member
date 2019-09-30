<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarBookingDetail extends BaseModel
{
    
    protected $table = 'dining_car_booking_detail';

    protected $primaryKey = 'id';


}
