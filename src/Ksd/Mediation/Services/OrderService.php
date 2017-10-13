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

    /**
     * 取得訂單資訊
     * @return mixed
     */
    public function info()
    {
        return $this->repository->setToken($this->userToken())->info();
    }

    /**
     * 取得訂單細項資訊
     * @param  $parameter
     * @return mixed
     */
    public function order($parameter)
    {
//        return $this->repository->order($parameter);
          return $this->repository->setToken($this->userToken())->order($parameter);
    }

    /**
     * 根據 條件篩選 取得訂單
     * @param $parameters
     * @return \Illuminate\Http\JsonResponse
     */
    public function search($parameters)
    {
        return $this->repository->setToken($this->userToken())->search($parameters);
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
}