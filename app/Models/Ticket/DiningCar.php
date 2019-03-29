<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;


use App\Enum\ClientType;
use App\Enum\DiningCarPointRecordType;
use App\Models\Gift;


class DiningCar extends BaseModel
{
    private $month;

    protected $appends = ['favorite'];
    protected $connection = 'backend';

    public function __construct()
    {
        $this->month = date('Y-m');
    }

    /**
     * 加入來源
     */
    public function getFavoriteAttribute()
    {
        return false;
    }

    /**
     * 取得範圍內的商店
     *
     * @param Bulider $query
     * @param array $longitude [min, max]
     * @param array $latitude [min, max]
     *
     * @return Bulider
     */
    public function scopeWithinLocation($query,
                                        $longitude,
                                        $latitude)
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
     * 取得封面圖
     */
    public function mainImg()
    {
        return $this->hasOne('App\Models\Ticket\DiningCarLogoImg');
    }

    /**
     * 取得封面圖
     */
    public function imgs()
    {
        return $this->hasMany('App\Models\Ticket\DiningCarImg');
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

    /**
     * 取得影音
     */
    public function media()
    {
        return $this->hasOne('App\Models\Ticket\DiningCarMedia');
    }

    /**
     * 取得會員卡
     */
    public function memberCard()
    {
        return $this->hasOne('App\Models\Ticket\DiningCarMember');
    }

    /**
     * 取得會員等級
     */
    public function memberLevels()
    {
        return $this->hasMany('App\Models\Ticket\DiningCarMemberLevel')->where('status',true);
    }

    /**
     * 取得禮物清單
     */
    public function gifts()
    {

        return $this
            ->hasMany(Gift::class, 'model_spec_id', 'id')
            ->where('model_type', ClientType::dining_car);

    }

    public function pointRules()
    {
        return $this->hasMany(DiningCarPointRule::class);

    }

}
