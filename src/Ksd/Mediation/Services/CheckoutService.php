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

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new CheckoutRepository($memberTokenService);
    }

    /**
     * 取得付款資訊
     * @param $source
     * @return array
     */
    public function info($source)
    {
        return $this->repository->info($source);
    }

    /**
     * 設定物流方式
     * @param $parameters
     */
    public function shipment($parameters)
    {
        $this->repository->shipment($parameters);
    }

    /**
     * 確定結帳
     * @param $parameters
     * @return array|mixed
     */
    public function confirm($parameters)
    {
        return $this->repository->confirm($parameters);
    }

    /**
     * 信用卡送金流
     * @param $parameters
     * @return array|mixed
     */
    public function creditCard($parameters)
    {
        return $this->repository->creditCard($parameters);
    }

}