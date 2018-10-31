<?php
/**
 * User: lee
 * Date: 2018/09/03
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Illuminate\Database\QueryException;

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

        $query = $this->model->with(['combo' => function($query) use ($parameter) {
                                if ($parameter->orderStatus === '1' || $parameter->orderStatus === '2')
                                return $query->orderBy('verified_at', 'desc');
                            }])
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
                                elseif ($parameter->orderStatus === '0') {
                                    return $query->where('order_detail_expire_due', '>=', date('Y-m-d H:i:s'))
                                        ->orWhere('order_detail_expire_due', null);
                                }
                            })
                            ->offset($parameter->offset())
                            ->limit($parameter->limit);

        switch ($parameter->orderStatus) {
            case '1':
            case '2':
                $orderDetails = $query->orderBy('verified_at', 'desc')->get();
                break;
            case '4':
                $orderDetails = $query->orderBy('ticket_gift_at', 'desc')->get();
                break;
            default:
                $orderDetails = $query->orderBy('created_at', 'desc')->get();
                break;
        }

        if ($orderDetails->isEmpty()) return null;

        return $orderDetails;
    }

    /**
     * 更新 發票相關
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateRecipient($id, $status, $price)
    {
        if (!$id) return false;

        try {
            return $this->model->where('order_detail_id', $id)
                                ->update([
                                    'recipient_status' => $status,
                                    'recipient_price' => $price
                                ]);
        } catch (QueryException $e) {
            return false;
        }
    }
}
