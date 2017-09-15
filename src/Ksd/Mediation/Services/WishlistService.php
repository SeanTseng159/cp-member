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

    public function items()
    {
        return $this->repository->setToken($this->userToken())->items();
    }

    public function add($parameter)
    {
        return $this->repository->setToken($this->userToken())->add($parameter);
    }

    public function delete($parameter)
    {
        return $this->repository->setToken($this->userToken())->delete($parameter);
    }

}