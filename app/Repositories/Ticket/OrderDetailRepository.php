<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderDetail;

class OrderDetailRepository extends BaseRepository
{

    public function __construct(OrderDetail $model)
    {
        $this->model = $model;
    }

    /**
     * 取票券列表
     * @param $lang
     * @param $parameter
     * @return mixed
     */
    public function all($lang, $parameter)
    {
        if (!$parameter) return null;

        $orderDetails = $this->model->where('member_id', $parameter->memberId)
                            ->where('ticket_show_status', 1)
                            ->where('verified_status', $parameter->status)
                            ->offset($parameter->offset())
                            ->limit($parameter->limit)
                            ->orderBy('created_at', 'desc')
                            ->get();

        if ($orderDetails->isEmpty()) return null;

        /*$orderDetails->transform(function ($detail) {
            if ()
            return $detail;
        });*/

        return $orderDetails;
    }
}
