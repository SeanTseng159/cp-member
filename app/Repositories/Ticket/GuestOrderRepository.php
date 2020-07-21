<?php
/**
 * User: lee
 * Date: 2020/07/12
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Exception;
use Illuminate\Database\QueryException;
use App\Repositories\BaseRepository;
use App\Models\Ticket\GuestOrder;

class GuestOrderRepository extends BaseRepository
{
    public function __construct(GuestOrder $model)
    {
        $this->model = $model;
    }

    /**
     * 新增
     * @param $orderId
     * @param $data
     * @return App\Repositories\Ticket\GuestOrder
     */
    public function create($orderId = 0, $orderer = [])
    {
        try {
            if (!$orderId) throw new Exception('GuestOrder OrderId Not Found');

            $model = new GuestOrder;
            $model->order_id = $orderId;
            $model->name = $orderer['name'];
            $model->countryCode = $orderer['countryCode'];
            $model->cellphone = $orderer['cellphone'];
            $model->country = $orderer['country'];
            $model->save();

            return $model;
        } catch (QueryException $e) {
            Logger::error('Create OrderShipment Error', $e->getMessage());
            return null;
        } catch (Exception $e) {
            Logger::error('Create OrderShipment Error', $e->getMessage());
            return null;
        }
    }

    /**
     * 訂單搜尋
     * @param $params
     * @return mixed
     */
    public function findByPhone($params)
    {
        try {
            $orderNo = $params->orderNo;
            return $this->model->with([
                                    'order',
                                    'order.details.combo',
                                    'order.shipment'
                                ])
                                ->whereHas('order', function ($query) use ($orderNo) {
                                    return $query->where('order_no', $orderNo)
                                                ->where('order_status', '!=', 2);
                                })
                                ->where($params->phoneNumber)
                                ->first();
        } catch (Exception $e) {
            Logger::error('findByPhone Error', $e->getMessage());
            return null;
        }
    }
}
