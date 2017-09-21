<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 3:08
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\CheckoutRepository;

class CheckoutService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new CheckoutRepository();
    }

    /**
     * 取得付款資訊
     * @return array
     */
    public function info()
    {
        return $this->repository->info();
    }

    /**
     * 確定結帳
     * @param $parameters
     */
    public function confirm($parameters)
    {
        $this->repository->confirm($parameters);
    }

}