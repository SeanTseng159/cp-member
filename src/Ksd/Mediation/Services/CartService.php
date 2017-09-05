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

    public function info()
    {
        return $this->repository->setToken($this->userToken())->info();
    }

    public function detail()
    {
        return $this->repository->setToken($this->userToken())->detail();
    }

    public function add($parameters)
    {
        return $this->repository->setToken($this->userToken())->add($parameters);
    }

    public function update($parameters)
    {
        return $this->repository->setToken($this->userToken())->update($parameters);
    }

    public function delete($parameters)
    {
        return $this->repository->setToken($this->userToken())->delete($parameters);
    }
}