<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:04
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Helper\MemberHelper;
use Ksd\Mediation\Repositories\CartRepository;

class CartService
{
    use MemberHelper;

    private $repository;

    public function __construct()
    {
        $this->repository = new CartRepository();
    }

    /**
     * 取得購物車簡易資訊
     * @return mixed
     */
    public function info()
    {
        return $this->repository->setToken($this->userToken())->info();
    }

    /**
     * 取得購物車資訊
     * @return mixed
     */
    public function detail()
    {
        return $this->repository->setToken($this->userToken())->detail();
    }

    /**
     * 商品加入購物車
     * @param $parameters
     */
    public function add($parameters)
    {
        return $this->repository->setToken($this->userToken())->add($parameters);
    }

    /**
     * 更新購物車內商品
     * @param $parameters
     */
    public function update($parameters)
    {
        return $this->repository->setToken($this->userToken())->update($parameters);
    }

    /**
     * 刪除購物車內商品
     * @param $parameters
     */
    public function delete($parameters)
    {
        return $this->repository->setToken($this->userToken())->delete($parameters);
    }
}