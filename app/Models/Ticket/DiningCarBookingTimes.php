<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarBookingTimes extends BaseModel
{
    use SoftDeletes;

    protected $table = 'dining_car_booking_times';

    protected $primaryKey = 'id';


}
