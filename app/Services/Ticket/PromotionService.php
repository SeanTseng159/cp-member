<?php
/**
 * User: lee
 * Date: 2018/12/05
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\PromotionRepository;

class PromotionService extends BaseService
{
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
     * @param $prodId
     * @param $specId
     * @param $specPriceId
     * @return mixed
     */
    public function product($prodId, $specId, $specPriceId)
    {
        return $this->repository->product($prodId, $specId, $specPriceId);
    }
}
