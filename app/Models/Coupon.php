<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Models\Ticket\BaseModel;

class Coupon extends BaseModel
{
    protected $table = 'coupons';

    public function __construct()
    {

    }

    /**
     * 取得已啟用且上架中的優惠券
     *
     * @param  $query
     *
     * @return
     */
    public function scopeIsActive($query)
    {
        $now = date('Y-m-d H:i:s');

        return $query->where('status', 1)
            ->where('on_sale_at', '<=', $now)
            ->where('off_sale_at', '>=', $now);
    }
}
