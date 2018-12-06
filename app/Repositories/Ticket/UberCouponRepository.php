<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\UberCoupon;

class UberCouponRepository extends BaseRepository
{

    public function __construct(UberCoupon $model)
    {
        $this->model = $model;
    }

    /**
     * 取資料
     * @return mixed
     */
    public function findByOrderDetailId($order_detail_id = 0)
    {
        return $this->model->where('order_detail_id', $order_detail_id)->first();
    }
}
