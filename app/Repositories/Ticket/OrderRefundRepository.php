<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Illuminate\Database\QueryException;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderRefund;

class OrderRefundRepository extends BaseRepository
{

    public function __construct(OrderRefund $model)
    {
        $this->missionModel = $model;
    }


    /**
     * 根據 Id 找單一退訂單
     * @param $orderNo
     * @return mixed
     */
    public function findByOrderId($orderId = 0)
    {
        if (!$orderId) return null;

        return $this->missionModel->where('order_id', $orderId)->first();
    }

    /**
     * 根據 Id 找單一退訂單
     * @param $id
     * @return mixed
     */
    public function find($id = 0)
    {
        if (!$id) return null;

        return $this->missionModel->find($id);
    }
}
