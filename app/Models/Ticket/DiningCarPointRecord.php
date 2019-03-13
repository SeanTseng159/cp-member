<?php
/**
 * User: Annie
 * Date: 2019/03/13
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;


use Carbon\Carbon;

class DiningCarPointRecord extends BaseModel
{
    public function __construct()
    {

    }

    public function scopeAllow($query)
    {
        return $query->where('status', 1)->where('expired_at','>=',Carbon::now());
    }


}
