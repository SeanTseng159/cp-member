<?php

namespace App\Models\AVR;


use App\Models\Ticket\ProductSpecPrice;
use Carbon\Carbon;

class Activity extends AVRBaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'avr_activities';

    /**
     * 上架中
     * @param $query
     * @return
     */
    public function scopeLaunched($query)
    {
        $now = Carbon::now();
        return $query
            ->where('on_shelf_time', '<=', $now)
            ->where('off_shelf_time', '>', $now)
            ->where('status', 1);
    }

    /**
     * 執行中
     * @param $query
     * @return
     */
    public function scopeExecute($query)
    {
        $now = Carbon::now();
        return $query
            ->where('start_activity_time', '<=', $now)
            ->where('end_activity_time', '>', $now)
            ->where('status', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);

    }

    public function missions()
    {
        return $this->hasMany(Mission::class)->orderBy('sort');
    }


    public function productPriceId()
    {
        return $this->hasOne(ProductSpecPrice::class, 'prod_spec_price_id', 'prod_spec_price_id');


    }


}
