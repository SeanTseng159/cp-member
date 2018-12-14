<?php
/**
 * User: lee
 * Date: 2018/12/14
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Promotion;
use App\Repositories\Ticket\ProductRepository;
use Carbon\Carbon;

class PromotionRepository extends BaseRepository
{
    protected $now;

    protected $productRepository;

    public function __construct(Promotion $model, ProductRepository $productRepository)
    {
        $this->model = $model;
        $this->productRepository = $productRepository;

        $this->now = Carbon::now()->toDateTimeString();
    }

    /**
     * 取資料
     * @param $id
     * @return App\Repositories\Ticket\Promotion
     */
    public function find($id = 0)
    {
        $promo = $this->model->with(['condition', 'prodSpecPrices'])
                            ->where('status', 1)
                            ->where('onshelf_time', '<=', $this->now)
                            ->where('offshelf_time', '>=', $this->now)
                            ->where('onsale_time', '<=', $this->now)
                            ->where('offsale_time', '>=', $this->now)
                            ->find($id);

        $products = [];
        foreach ($promo->prodSpecPrices as $row) {
            $prod = $this->productRepository->findByCheckout($row->prod_id, $row->spec_id, $row->price_id);

            $prod->marketPrice = $row->price;
            $prod->marketStock = $row->stock;

            $products[] = $prod;
        }

        $promo->products = $products;

        return $promo;
    }
}
