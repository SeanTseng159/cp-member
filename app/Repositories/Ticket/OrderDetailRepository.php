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
    protected $memberModel;

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
    public function tickets($lang, $parameter)
    {
        if (!$parameter) return null;

        $orderDetails = $this->model->with('combo')
                            ->where('member_id', $parameter->memberId)
                            ->where('ticket_show_status', 1)
                            ->whereIn('prod_type', [1, 2, 3])
                            ->where(function($query) use ($parameter) {
                                if ($parameter->orderStatus === '4') {
                                    return $query->where('order_detail_member_id', '!=', $parameter->memberId);
                                }
                                else {
                                    return $query->where('order_detail_member_id', $parameter->memberId)->where('verified_status', $parameter->status);
                                }
                            })
                            ->where(function($query) use ($parameter) {
                                if ($parameter->orderStatus === '3') {
                                    return $query->where('order_detail_expire_due', '<=', date('Y-m-d H:i:s'));
                                }
                                elseif ($parameter->orderStatus === '0' || $parameter->orderStatus === '1') {
                                    return $query->where('order_detail_expire_due', '>=', date('Y-m-d H:i:s'))
                                        ->orWhere('order_detail_expire_due', null);
                                }
                            })
                            ->offset($parameter->offset())
                            ->limit($parameter->limit)
                            ->orderBy('created_at', 'desc')
                            ->get();

        if ($orderDetails->isEmpty()) return null;

        return $orderDetails;
    }
}
