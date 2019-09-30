<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarBookingLimit extends BaseModel
{
    

    protected $table = 'dining_car_booking_limit';

    protected $primaryKey = 'id';


}
