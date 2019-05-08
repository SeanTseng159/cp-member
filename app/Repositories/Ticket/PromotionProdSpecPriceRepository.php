<?php
/**
 * User: lee
 * Date: 2018/12/12
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\PromotionProdSpecPrice;

class PromotionProdSpecPriceRepository extends BaseRepository
{
    public function __construct(PromotionProdSpecPrice $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 根據 規格/票種 撈商品
     * @param $promotionId
     * @param $prodId
     * @param $specId
     * @param $priceId
     * @return mixed
     */
    public function findBySpecPrice($promotionId = 0, $prodId = 0, $specId = 0, $priceId = 0)
    {
        return $this->missionModel->where('promotion_id', $promotionId)
                            ->where('prod_id', $prodId)
                            ->where('spec_id', $specId)
                            ->where('price_id', $priceId)
                            ->first();
    }
}
