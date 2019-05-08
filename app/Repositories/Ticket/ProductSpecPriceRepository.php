<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ProductSpecPrice;

class ProductSpecPriceRepository extends BaseRepository
{
    public function __construct(ProductSpecPrice $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 根據 id 更新
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data = [])
    {
        if (!$data) return false;

        return $this->missionModel->where('prod_spec_price_id', $id)
                            ->update($data);
    }
}
