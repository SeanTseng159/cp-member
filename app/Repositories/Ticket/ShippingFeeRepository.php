<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ShippingFee;

class ShippingFeeRepository extends BaseRepository
{

    public function __construct(ShippingFee $model)
    {
        $this->model = $model;
    }

    /**
     * 取選單資料
     * @param $productId
     * @return mixed
     */
    public function allByProductId($productId)
    {
        return $this->model->where('prod_id', $productId)
                            ->orderBy('lower', 'asc')
                            ->get();
    }
}
