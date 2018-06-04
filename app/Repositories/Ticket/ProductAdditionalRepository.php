<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ProductAdditional;
use App\Repositories\Ticket\ProductRepository;

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
        $additionals = $this->model->notDeleted()->where('prod_id', $id)->orderBy('prod_additional_sort', 'asc')->get();

        if ($additionals) {
            $productRepository = app()->build(ProductRepository::class);

            foreach ($additionals as $k => $additional) {
                $additionals[$k]->product = $productRepository->find($additional->prod_additional_prod_id);
            }

            return $additionals;
        }

        return null;
    }
}
