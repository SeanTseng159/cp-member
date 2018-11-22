<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ProductImgRepository;

class ProductImgService extends BaseService
{
    public function __construct(ProductImgRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $productId
     * @return mixed
     */
    public function findMain($productId)
    {
        return $this->repository->findMain($productId);
    }
}
