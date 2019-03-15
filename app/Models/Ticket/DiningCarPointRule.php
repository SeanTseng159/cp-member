<?php

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarPointRule extends BaseModel
{
	use SoftDeletes;

    public function __construct()
    {

    }

    public function scopeIsActive($query)
    {
        $now = date('Y-m-d H:i:d');

        return $query->where('status', 1)
            ->where(function($query) use ($now) {
            	$query->where('start_time', '<=', $now)
            		->where('end_time', '>=', $now);
            })
            ->orWhere(function($query) {
            	$query->whereNull('start_time')
            		->whereNull('end_time');
            });
    }
}
