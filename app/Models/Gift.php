<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Enum\ClientType;
use Carbon\Carbon;
use App\Models\Ticket\BaseModel;
use App\Models\Ticket\DiningCar;


class Gift extends BaseModel
{
    protected $table = 'gifts';
    protected $clientType = ['dining_car'];
    protected $connection = 'backend';

    public function __construct()
    {

    }

    public function diningCar()
    {
        return $this->hasOne(DiningCar::class, 'id', 'model_spec_id');
    }

    /**
     * 取得已啟用且上架中的禮物
     *
     * @param  $query
     *
     * @return
     */
    public function scopeIsActive($query)
    {
        $now = Carbon::now();

        return $query->where('status', 1)
            ->where('on_sale_at', '<=', $now)
            ->where('off_sale_at', '>=', $now);
    }

    /**
     * 可兌換的禮物
     * @param $query
     * @return
     */

    public function scopeExchangable($query)
    {
        $now = Carbon::now();

        return $query->where('status', 1)
            ->where('start_at', '<=', $now)
            ->where('expire_at', '>=', $now);

    }

    /**
     * 屬於餐車的禮物
     * @param $query
     * @return
     */

    public function scopeIsDiningCar($query)
    {
        return $query->where('model_type', ClientType::dining_car);
    }

    /*
     * 取得禮物券
     *
     *
     */
    public function memberGiftItems()
    {
        return $this->hasMany('App\Models\Ticket\MemberGiftItem');

    }

}
