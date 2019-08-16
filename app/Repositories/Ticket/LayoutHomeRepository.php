<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\LayoutHome;
use App\Repositories\Ticket\ProductRepository;
use App\Repositories\Ticket\PromotionRepository;
use App\Repositories\MagentoProductRepository;

class LayoutHomeRepository extends BaseRepository
{
    protected $productRepository;
    protected $MagentoProductRepository;

    public function __construct(LayoutHome $model, 
                                ProductRepository $productRepository, 
                                MagentoProductRepository $MagentoProductRepository,
                                PromotionRepository $promotionRepository
                                )
    {
        $this->model = $model;
        $this->productRepository = $productRepository;
        $this->MagentoProductRepository = $MagentoProductRepository;
        $this->PromotionRepository = $promotionRepository;
    }

    /**
     * 取首頁產品
     * @return mixed
     */
    public function all($lang)
    {
        $data = $this->model->with(['products' => function($query) {
                                return $query->notDeleted()
                                            ->orderBy('layout_home_prod_sort', 'asc');
                            }])
                            ->notDeleted()
                            ->where('layout_home_lang', $lang)
                            ->where('layout_home_status', 1)
                            ->orderBy('layout_home_sort', 'asc')
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
                    elseif ($product->source === 3){
                        $prod = $this->PromotionRepository->find($product->prod_id);
                    }

                    if ($prod) $products[] = $prod;
                }

                $row->items = $products;

                return $row;
            });
        }

        return $data;
    }
}
