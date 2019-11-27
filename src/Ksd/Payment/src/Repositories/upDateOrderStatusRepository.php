<?php
/**
 * User: Lee
 * Date: 2018/07/10
 * Time: 下午2:20
 */

namespace Ksd\Payment\Repositories;



use Exception;
use Log;
use App\Repositories\BaseRepository;
use App\Models\Ticket\Order;
use App\Models\Ticket\OrderDetail;
use Carbon\Carbon;
class upDateOrderStatusRepository  extends BaseRepository
{

    protected $order;
    protected $orderDetail;
    public function __construct(Order $order,
                                OrderDetail $orderDetail)
    {
        $this->order = $order;
        $this->orderDetail = $orderDetail;
    }

    /**
     * 依據訂單編號 更新
     * @param $id
     * @param $data
     * @return mixed
     */
    public function upDateOderByOrderNo($orderNo, $data)
    {
        if (!$data) return false;

        try {
            $this->order->where('order_no', $orderNo)->update($data);
        } catch (QueryException $e) {
            return false;
        }
    }
    /**
     * 依據訂單編號 更新
     * @param $id
     * @param $data
     * @return mixed
     */
    public function upDateOderDeailByOrderNo($orderNo, $data)
    {
        if (!$data) return false;

        try {

            $this->orderDetail->where('order_no', $orderNo)->update($data);

        } catch (QueryException $e) {
            return false;
        }
    }
}

