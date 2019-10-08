<?php
/**
 * User: lee
 * Date: 2018/12/14
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\Promotion;
use App\Repositories\Ticket\PromotionProdSpecPriceRepository as PPSPRepository;
use Carbon\Carbon;

class PromotionRepository extends BaseRepository
{
    protected $now;

    protected $productRepository;
    protected $ppspRepository;
    protected $tagProdRepository;
    protected $model;

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
        $promo = $this->model->with(['conditions', 'prodSpecPrices', 'shipping', 'banner', 'prodSpecPrices.proudct.img'])
                            ->where('status', 1)
                            ->where('onshelf_time', '<=', $this->now)
                            ->where('offshelf_time', '>=', $this->now)
                            ->find($id);

        $products = [];

        if(!$promo)
            return null;

        foreach ($promo->prodSpecPrices as $row) {
            // 庫存不存，排除
            if ($row->stock <= 0) continue;

            $prod = $this->productRepository->findByCheckout($row->prod_id, $row->spec_id, $row->price_id);

            // 商品不可銷售，排除
            if (!$prod) continue;

            $prod->marketPrice = $row->price;
            $prod->marketStock = $row->stock;

            $products[] = $prod;
        }

        $promo->products = $products;

        return $promo;
    }

    /**
     * 根據 商品 id 規格/票種 取得商品明細
     * @param $promotionId
     * @param $prodId
     * @param $specId
     * @param $specPriceId
     * @param $hasTag
     * @return mixed
     */
    public function product($promotionId, $prodId, $specId, $specPriceId, $hasTag = false)
    {
        $prod = $this->productRepository->findByCheckout($prodId, $specId, $specPriceId);
        $promotionProd = $this->ppspRepository->findBySpecPrice($promotionId, $prodId, $specId, $specPriceId);

        if ($prod && $promotionProd) {
            $prod->prod_spec_price_value = $promotionProd->price ?: $prod->prod_spec_price_value;
            $prod->marketStock = $promotionProd->stock;
        }

        if ($hasTag) {
            $prod->tags = $this->tagProdRepository->getTagsByProdId($prodId);
        }

        return $prod;
    }

    public function search($keyword)
    {
        $promo = $this->model->with(['conditions', 'prodSpecPrices', 'shipping', 'prodSpecPrices.proudct.img'])
            ->where('status', 1)
            ->where('onshelf_time', '<=', $this->now)
            ->where('offshelf_time', '>=', $this->now)
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('sub_title', 'like', '%' . $keyword . '%');
            })
            ->get();
        return $promo;

    }
}
