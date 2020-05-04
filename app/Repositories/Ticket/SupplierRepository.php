<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;

use App\Models\Ticket\Supplier;

class SupplierRepository extends BaseRepository
{
    protected $type = 1;

    public function __construct(Supplier $model)
    {
        $this->model = $model;
    }


    public function easyFind($id)
    {
        return $this->model->with('employee.diningCar')
            ->where('supplier_id', $id)
            ->first();
    }
}
