<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:56
 */


namespace Ksd\Mediation\Services;


use Ksd\Mediation\Helper\MemberHelper;
use Ksd\Mediation\Repositories\OrderRepository;

class OrderService
{
    use MemberHelper;

    private $repository;

    public function __construct()
    {
        $this->repository = new OrderRepository();
    }

    public function info()
    {
        return $this->repository->setToken($this->userToken())->info();
    }

    public function order($parameter)
    {
        return $this->repository->order($parameter);
    }

    public function search($parameters)
    {
        return $this->repository->setToken($this->userToken())->search($parameters);
    }


}