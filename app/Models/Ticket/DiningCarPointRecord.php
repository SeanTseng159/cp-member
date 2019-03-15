<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class DiningCarPointRecord extends BaseModel
{
    public function __construct()
    {

    }

    public function scopeIsEffective($query)
    {
    	$now = date('Y-m-d H:i:d');

        return $query->where('status', 1)
            ->where(function($query) use ($now) {
            	$query->where('expired_at', '>=', $now)
            		->orWhereNull('expired_at');
            });
    }
}
