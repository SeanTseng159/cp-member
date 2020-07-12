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
}
