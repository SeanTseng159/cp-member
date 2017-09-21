<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/14
 * Time: 下午 04:42
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Helper\MemberHelper;
use Ksd\Mediation\Repositories\WishlistRepository;


class WishlistService
{

    use MemberHelper;

    private $repository;

    public function __construct()
    {
        $this->repository = new WishlistRepository();
    }

    /**
     * 取得所有收藏列表
     * @return mixed
     */
    public function items()
    {
        return $this->repository->setToken($this->userToken())->items();
    }

    /**
     * 根據商品id 增加商品至收藏清單
     * @param $parameter
     */
    public function add($parameter)
    {
        return $this->repository->setToken($this->userToken())->add($parameter);
    }

    /**
     * 根據商品id 刪除收藏清單商品
     * @param $parameter
     */
    public function delete($parameter)
    {
        return $this->repository->setToken($this->userToken())->delete($parameter);
    }

}