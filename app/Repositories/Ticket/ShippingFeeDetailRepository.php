<?php
/**
 * User: lee
 * Date: 2020/07/10
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ShippingFeeDetail;

class ShippingFeeDetailRepository extends BaseRepository
{

    public function __construct(ShippingFeeDetail $model)
    {
        $this->model = $model;
    }

    /**
     * 依供應商取運費
     * @param $supplierId
     * @return mixed
     */
    public function findBySupplierId($supplierId)
    {
        return $this->model->where('supplier_id', $supplierId)
                            ->first();
    }
}
