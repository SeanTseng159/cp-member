<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiningCarPointRule extends BaseModel
{
	use SoftDeletes;

    protected $dates = ['deleted_at'];

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
            });
    }
}
