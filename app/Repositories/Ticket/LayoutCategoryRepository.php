<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\LayoutCategory;
use App\Repositories\Ticket\ProductRepository;
use App\Repositories\MagentoProductRepository;

class LayoutCategoryRepository extends BaseRepository
{
    protected $productRepository;
    protected $MagentoProductRepository;

    public function __construct(LayoutCategory $model, ProductRepository $productRepository, MagentoProductRepository $MagentoProductRepository)
    {
        $this->model = $model;
        $this->productRepository = $productRepository;
        $this->MagentoProductRepository = $MagentoProductRepository;
    }

    /**
     * 取產品
     * @param $lang
     * @param $id
     * @return mixed
     */
    public function allById($lang, $id)
    {
        $data = $this->model->with(['products' => function($query) {
                                return $query->notDeleted()
                                            ->orderBy('layout_category_prod_sort', 'asc');
                            }])
                            ->notDeleted()
                            ->where('tag_id', $id)
                            ->orderBy('layout_category_sort', 'asc')
                            ->get();

        if ($data) {
            $data->transform(function ($row, $key) {
                $products = [];

                foreach ($row->products as $product) {
                    if ($product->source === 1) {
                        $prod = $this->productRepository->easyFind($product->prod_id, true);
                    }
                    elseif ($product->source === 2) {
                        $prod = $this->MagentoProductRepository->find($product->prod_id);
                    }

                    if ($prod) $products[] = $prod;
                }

                $row->items = $products;

                return $row;
            });

            $data = $data->reject(function ($row) {
                return (!$row->items);
            });
        }

        return $data;
    }
}
