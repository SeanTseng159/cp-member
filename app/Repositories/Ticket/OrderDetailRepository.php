<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Illuminate\Database\QueryException;

use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderDetail;

class OrderDetailRepository extends BaseRepository
{

    public function __construct(OrderDetail $model)
    {
        $this->model = $model;
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