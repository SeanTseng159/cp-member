<?php
/**
 * User: lee
 * Date: 2018/11/21
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ProductImg;

class ProductImgRepository extends BaseRepository
{
    public function __construct(ProductImg $model)
    {
        $this->model = $model;
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $productId
     * @return mixed
     */
    public function findMain($productId = 0)
    {
        return $this->model->where('prod_id', $productId)->orderBy('img_sort')->first();
    }
}
