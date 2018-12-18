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
use App\Repositories\Ticket\PromotionProdSpecPriceRepository as PPSPRepository;
use App\Repositories\Ticket\TagProdRepository;
use Carbon\Carbon;

class PromotionRepository extends BaseRepository
{
    protected $now;

    protected $productRepository;
    protected $ppspRepository;
    protected $tagProdRepository;

    public function __construct(Promotion $model,
                                ProductRepository $productRepository,
                                PPSPRepository $ppspRepository,
                                TagProdRepository $tagProdRepository)
    {
        $this->model = $model;
        $this->productRepository = $productRepository;
        $this->ppspRepository = $ppspRepository;
        $this->tagProdRepository = $tagProdRepository;

        $this->now = Carbon::now()->toDateTimeString();
    }

    /**
     * 取資料
     * @param $id
     * @return App\Repositories\Ticket\Promotion
     */
    public function find($id = 0)
    {
        $promo = $this->model->with(['conditions', 'prodSpecPrices', 'shipping'])
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

    /**
     * 根據 商品 id 規格/票種 取得商品明細
     * @param $prodId
     * @param $specId
     * @param $specPriceId
     * @param $hasTag
     * @return mixed
     */
    public function product($prodId, $specId, $specPriceId, $hasTag = false)
    {
        $prod = $this->productRepository->findByCheckout($prodId, $specId, $specPriceId);
        $promotionProd = $this->ppspRepository->findBySpecPrice($prodId, $specId, $specPriceId);

        if ($prod && $promotionProd) {
            $prod->prod_spec_price_value = $promotionProd->price ?: $prod->prod_spec_price_value;
            $prod->marketStock = $promotionProd->stock;
        }

        if ($hasTag) {
            $prod->tags = $this->tagProdRepository->getTagsByProdId($prodId);
        }

        return $prod;
    }
}
