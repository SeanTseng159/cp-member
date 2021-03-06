<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;

use App\Repositories\Ticket\ProductRepository;

use App\Traits\ProductHelper;

class ProductService extends BaseService
{
    use ProductHelper;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * 根據 商品 id 取得商品基本資料
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
        return $this->repository->findById($id);
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
     * 根據 商品 id 取得所有加購商品明細
     * @param $id
     * @return mixed
     */
    public function findPurchaseOnShelf($id)
    {
        $onShelf = true;
        return $this->repository->findPurchase($id, $onShelf);
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

    /**
     * 根據 商品 id 取得商品明細 (結帳用) [只取 規格]
     * @param $id
     * @param $specId
     * @param $specPriceId
     * @param $hasTag
     * @return mixed
     */
    public function findByCheckout($id, $specId, $specPriceId, $hasTag = false)
    {
        $product = $this->repository->findByCheckout($id, $specId, $specPriceId, $hasTag);

        // 檢查是否在合理的使用期限內
        if ($product && !$this->checkExpire($product)) return NULL;

        return $product;
    }

    /**
     * [New] 根據 商品 id 取得商品明細 (結帳用) [只取 規格]
     * @param $id
     * @param $specId
     * @param $specPriceId
     * @return mixed
     */
    public function findByCheckout2($id, $specId, $specPriceId)
    {
        $product = $this->repository->findByCheckout2($id, $specId, $specPriceId);

        // 檢查是否在合理的使用期限內
        if ($product && !$this->checkExpire($product)) return NULL;

        return $product;
    }

    /**
     * 根據 商品 id 取得加購商品明細 (結帳用) [只取 規格]
     * @param $id
     * @param $specId
     * @param $specPriceId
     * @param $hasTag
     * @return mixed
     */
    public function findAdditionalByCheckout($id, $specId, $specPriceId, $hasTag = false)
    {
        $product = $this->repository->findAdditionalByCheckout($id, $specId, $specPriceId, $hasTag);

        // 檢查是否在合理的使用期限內
        if ($product && !$this->checkExpire($product)) return NULL;

        return $product;
    }

    /**
     * 根據 商品 id 取得加購商品明細 (結帳用) [只取 規格]
     * @param $id
     * @param $specId
     * @param $specPriceId
     * @return mixed
     */
    public function findAdditionalByCheckout2($id, $specId, $specPriceId)
    {
        $product = $this->repository->findAdditionalByCheckout2($id, $specId, $specPriceId);

        // 檢查是否在合理的使用期限內
        if ($product && !$this->checkExpire($product)) return NULL;

        return $product;
    }

    /**
     * 依 關鍵字 找商品
     * @param $keyword
     * @return mixed
     */
    public function search($keyword)
    {
        return $this->repository->search($keyword);
    }
}
