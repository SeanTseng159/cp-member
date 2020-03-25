<?php

/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\TagProduct;

class TagProdRepository extends BaseRepository
{

    public function __construct(TagProduct $model)
    {
        $this->model = $model;
    }

    /**
     * 依據商品取分類
     * @return mixed
     */
    public function getTagsByProdId($productId)
    {
        return $this->model->where('prod_id', $productId)
            ->get();
    }
}
