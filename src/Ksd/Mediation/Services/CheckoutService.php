<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 3:08
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\CheckoutRepository;
use App\Services\TspgPostbackService;

class CheckoutService
{
    protected $repository;

    public function __construct(MemberTokenService $memberTokenService,TspgPostbackService $tspgPostbackService)
    {
        $this->repository = new CheckoutRepository($memberTokenService,$tspgPostbackService);
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
     * @return bool
     */
    public function shipment($parameters)
    {
        return $this->repository->shipment($parameters);
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
     * 信用卡送金流(藍新)
     * @param $parameters
     * @return array|mixed
     */
    public function creditCard($parameters)
    {
        return $this->repository->creditCard($parameters);
    }

    /**
     * 信用卡送金流(台新)
     * @param $parameters
     * @return array|mixed
     */
    public function transmit($parameters)
    {
        return $this->repository->transmit($parameters);
    }

    /**
     * 接收台新信用卡前台通知程式 post_back_url
     * @param $parameters
     * @return array|mixed
     */
    public function postBack($parameters)
    {
        return $this->repository->postBack($parameters);
    }

    /**
     * 接收台新信用卡後台通知程式 result_url
     * @param $parameters
     * @return array|mixed
     */
    public function result($parameters)
    {
        return $this->repository->result($parameters);
    }

}