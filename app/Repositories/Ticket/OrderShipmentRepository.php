<?php
/**
 * User: lee
 * Date: 2018/12/14
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use Exception;
use Illuminate\Database\QueryException;
use App\Repositories\BaseRepository;
use App\Models\Ticket\OrderShipment;

class OrderShipmentRepository extends BaseRepository
{
    public function __construct(OrderShipment $model)
    {
        $this->model = $model;
    }

    /**
     * 新增
     * @param $orderId
     * @param $data
     * @return App\Repositories\Ticket\OrderShipment
     */
    public function createForOrder($orderId = 0, $shipment = [])
    {
        try {
            if (!$orderId) throw new Exception('OrderShipment OrderId Not Found');

            $orderShipment = new OrderShipment;
            $orderShipment->order_id = $orderId;
            $orderShipment->user_name = $shipment['userName'];
            $orderShipment->country_code = $shipment['countryCode'];
            $orderShipment->cellphone = $shipment['cellphone'];
            $orderShipment->zipcode = $shipment['zipcode'];
            $orderShipment->address = $shipment['address'];
            $orderShipment->method = 1;
            $orderShipment->status = 1;
            $orderShipment->save();

            return $orderShipment;
        } catch (QueryException $e) {
            Logger::error('Create OrderShipment Error', $e->getMessage());
            return null;
        } catch (Exception $e) {
            Logger::error('Create OrderShipment Error', $e->getMessage());
            return null;
        }
    }
}
