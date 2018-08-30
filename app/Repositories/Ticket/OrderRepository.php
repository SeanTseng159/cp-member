<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Illuminate\Database\QueryException;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Order;

class OrderRepository extends BaseRepository
{

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     * 更新 發票狀態
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateRecipientStatus($id, $status)
    {
        if (!$id) return false;

        try {
            return $this->model->where('order_id', $id)
                                ->update([
                                    'order_recipient_status' => $status
                                ]);
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 根據 No 找單一訂單
     * @param $orderNo
     * @return mixed
     */
    public function findByOrderNo($orderNo = 0)
    {
        if (!$orderNo) return null;

        return $this->model->where('order_no', $orderNo)->first();
    }

    /**
     * 根據 會員 id 取得訂單列表
     * @param $memberId
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getMemberOrdersByDate($memberId, $startDate, $endDate)
    {
        if (!$memberId) return null;

        $orders = $this->model->with(['detail' => function($query) {
                                    $query->where('prod_type', '!=', 4);
                                }, 'detail.productImg' => function($query) {
                                    $query->orderBy('img_sort', 'asc');
                            }])
                            ->notDeleted()
                            ->where('order_status', '!=', 2)
                            ->when($memberId, function($query) use ($memberId) {
                                $query->where('member_id', $memberId);
                            })
                            ->when($startDate, function($query) use ($startDate) {
                                $query->where('created_at', '>=', $startDate);
                            })
                            ->when($endDate, function($query) use ($endDate) {
                                $query->where('created_at', '<=', $endDate);
                            })
                            ->orderBy('created_at', 'desc')
                            ->get();

        return $orders;
    }

    /**
     * 依據 發票狀態 取得付款成功訂單列表
     * @param $status
     * @param $recipientStatus
     * @return mixed
     */
    public function getOrdersByRecipientStatus($status = 10, $recipientStatus = 99)
    {
        $orders = $this->model->with(['detail', 'detail.productSpecPrice'])
                                ->notDeleted()
                                ->where('order_no', 'like', '%20180828%')
                                ->where('order_status', $status)
                                ->where('order_recipient_status', $recipientStatus)
                                ->get();

        return $orders;
    }
}
