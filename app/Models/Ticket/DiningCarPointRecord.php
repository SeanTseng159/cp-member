<?php
/**
 * User: Annie
 * Date: 2019/03/13
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Ticket\BaseModel;
use App\Enum\DiningCarPointRecordType;
use App\Models\Gift;
use Carbon\Carbon;

class DiningCarPointRecord extends BaseModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

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

    public function diningCar()
    {
        return $this->belongsTo(DiningCar::class);

    }

    public function pointRules()
    {
        return $this->belongsTo(DiningCarPointRule::class, 'model_spec_id', 'id');

    }

    public function gifts()
    {
        return $this->belongsTo(Gift::class, 'model_spec_id', 'id');

    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeAllow($query)
    {

        return $query->where('status', 1)
            ->where(function ($query) {
                $query->where('expired_at', '>=', Carbon::now())
                    ->orWhereNull('expired_at');
            });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
