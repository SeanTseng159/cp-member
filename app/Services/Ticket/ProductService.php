<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ProductRepository;

class ProductService extends BaseService
{
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 根據 商品 id 取得商品明細
     * @param $id
     * @param $memberId
     * @return mixed
     */
    public function findOnShelf($id, $memberId = NULL)
    {
        $onShelf = true;
        return $this->repository->find($id, $onShelf, $memberId);
    }

    /**
     * 根據 組合商品(內容物) id 取得商品明細
     * @param $id
     * @return mixed
     */
    public function findComboItemOnShelf($id)
    {
        $onShelf = true;
        return $this->repository->findComboItem($id, $onShelf);
    }
}
