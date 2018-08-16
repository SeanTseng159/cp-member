<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ProductAdditional;

class ProductAdditionalRepository extends BaseRepository
{
    public function __construct(ProductAdditional $model)
    {
        $this->model = $model;
    }

    /**
     * 取得加購商品底下所有產品
     * @param $id
     * @return array | null
     */
    public function getAllByProdId($id)
    {
        $additionals = $this->model->with(['product.specs.specPrices', 'product.imgs' => function($query) {
                                        return $query->orderBy('img_sort')->first();
                                    }])
                                    ->whereHas('product', function($query) {
                                        return $query->where('prod_onshelf', 1);
                                    })
                                    ->notDeleted()
                                    ->where('prod_id', $id)
                                    ->orderBy('prod_additional_sort', 'asc')
                                    ->get();

        return $additionals ?: null;
    }
}
