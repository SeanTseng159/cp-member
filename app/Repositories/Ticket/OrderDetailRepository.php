<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderDetail;
use App\Models\Member;

class OrderDetailRepository extends BaseRepository
{
    protected $memberModel;

    public function __construct(OrderDetail $model, Member $memberModel)
    {
        $this->model = $model;
        $this->memberModel = $memberModel;
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

        $orderDetails = $this->model->where('order_detail_member_id', $parameter->memberId)
                            ->where('ticket_show_status', 1)
                            ->where('verified_status', $parameter->status)
                            ->where(function($query) {
                                return $query->where('order_detail_expire_due', '>=', date('Y-m-d H:i:s'))
                                    ->orWhere('order_detail_expire_due', null);
                            })
                            ->offset($parameter->offset())
                            ->limit($parameter->limit)
                            ->orderBy('created_at', 'desc')
                            ->get();

        if ($orderDetails->isEmpty()) return null;

        // 把組合商品的子商品拉到主商品底下
        $member = $this->memberModel->find($parameter->memberId);

        $orderDetails->transform(function ($detail) use ($orderDetails, $member) {
            $detail->member = $member;

            if ($detail->prod_type === 2) {
                $detail->combo = $orderDetails->where('order_no', $detail->order_no)
                                            ->where('order_detail_addnl_seq', $detail->order_detail_seq)->where('prod_type', 4)
                                            ->all();
            }

            return $detail;
        });

        $orderDetails = $orderDetails->filter(function ($detail) {
            return $detail->prod_type !== 4;
        });

        return $orderDetails;
    }
}
