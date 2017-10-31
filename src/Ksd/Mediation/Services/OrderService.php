<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:56
 */


namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\OrderRepository;

class OrderService
{
    private $repository;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new OrderRepository($memberTokenService);
    }

    /**
     * 取得訂單資訊
     * @return mixed
     */
    public function info()
    {
        return $this->repository->info();
    }

    /**
     * 取得訂單細項資訊
     * @param  $parameter
     * @return mixed
     */
    public function order($parameter)
    {

          return $this->repository->order($parameter);
    }

    /**
     * 根據 條件篩選 取得訂單
     * @param $parameters
     * @return \Illuminate\Http\JsonResponse
     */
    public function search($parameters)
    {
        return $this->repository->search($parameters);
    }

    /**
     * 根據 id 查詢訂單
     * @param $parameters
     * @return \Ksd\Mediation\Result\OrderResult
     */
    public function find($parameters)
    {
        return $this->repository->find($parameters);
    }

    /**
     * 接收ATM繳款通知程式
     * @param $parameters
     * @return \Illuminate\Http\JsonResponse
     */
    public function writeoff($parameters)
    {
        return $this->repository->writeoff($parameters);
    }
}