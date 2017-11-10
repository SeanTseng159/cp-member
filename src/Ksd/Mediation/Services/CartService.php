<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:04
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\CartRepository;


class CartService
{
    private $repository;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new CartRepository($memberTokenService);
    }

    /**
     * 取得購物車簡易資訊
     * @return mixed
     */
    public function info()
    {
        return $this->repository->info();
    }

    /**
     * 取得購物車資訊
     * @return mixed
     */
    public function detail()
    {
        return $this->repository->detail();
    }

    /**
     * 商品加入購物車
     * @param $parameters
     */
    public function add($parameters)
    {
        return $this->repository->add($parameters);
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     */
    public function update($parameters)
    {
        return $this->repository->update($parameters);
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     */
    public function delete($parameters)
    {
        return $this->repository->delete($parameters);
    }

    /**
     * 刪除購物車快取
     * @param $parameters
     */
    public function cleanCache()
    {
        return $this->repository->cleanCache();
    }
}
