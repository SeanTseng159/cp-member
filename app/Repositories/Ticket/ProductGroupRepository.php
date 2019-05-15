<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ProductGroup;

class ProductGroupRepository extends BaseRepository
{
    public function __construct(ProductGroup $model)
    {
        $this->model = $model;
    }

    /**
     * 取得主商品底下所有組合商品
     * @param $id
     * @return array | null
     */
    public function getAllByProdId($id)
    {
        return $this->model->with('product')->notDeleted()->where('prod_id', $id)->orderBy('prod_group_sort', 'asc')->get();
    }
}
