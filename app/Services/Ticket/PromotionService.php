<?php
/**
 * User: lee
 * Date: 2018/12/05
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\PromotionRepository;

use App\Traits\ProductHelper;

class PromotionService extends BaseService
{
    use ProductHelper;

    public function __construct(PromotionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取資料
     * @param $id
     * @return App\Repositories\Ticket\Promotion
     */
    public function find($id = 0)
    {
        return $this->repository->find($id);
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
        $product = $this->repository->product($promotionId, $prodId, $specId, $specPriceId, $hasTag);

        // 檢查是否在合理的使用期限內
        if ($product && !$this->checkExpire($product)) return NULL;

        return $product;
    }
}
