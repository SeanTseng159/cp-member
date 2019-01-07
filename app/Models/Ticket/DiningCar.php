<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class DiningCar extends BaseModel
{
    private $month;

    public function __construct()
    {
        $this->month = date('Y-m');
    }

    /**
     * 取得範圍內的商店
     * @param Bulider $query
     * @param array $longitude  [min, max]
     * @param array $latitude   [min, max]
     * @return Bulider
     */
    public function scopeWithinLocation($query, $longitude, $latitude)
    {
        return $query->whereBetween('longitude', $longitude)
                     ->whereBetween('latitude', $latitude);
    }

    /**
     * 取得主分類
     */
  	public function category()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCarCategory', 'dining_car_category_id');
    }

    /**
     * 取得次分類
     */
  	public function subCategory()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCarCategory', 'dining_car_sub_category_id');
    }

    /**
     * 取得社群網址
     */
    public function socialUrls()
    {
        return $this->hasMany('App\Models\Ticket\DiningCarSocialUrl')->where('status', 1);
    }

    /**
     * 取得營業時間
     */
    public function businessHoursDays()
    {
        return $this->hasMany('App\Models\Ticket\DiningCarBusinessHoursDay')->where('status', 1)->orderBy('day');
    }

    /**
     * 取得營業時間
     */
    public function businessHoursDates()
    {
        return $this->hasMany('App\Models\Ticket\DiningCarBusinessHoursDate')
                    ->where('business_date', 'like', $this->month . '%');
    }
}
