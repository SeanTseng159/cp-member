<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/5
 * Time: 下午 02:52
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Repositories\MyTicketRepository;

class MyTicketService
{


    private $repository;

    public function __construct()
    {
        $this->repository = new MyTicketRepository();
    }
    /**
     * 取得票券使用說明
     * @return array
     */
    public function help()
    {
        return $this->repository->help();
    }

    /**
     * 取得票券列表
     * @param  $parameter
     * @return array
     */
    public function info($parameter)
    {
        return $this->repository->info($parameter);
    }

    /**
     * 利用票券id取得細項資料
     * @param  $parameter
     * @return array
     */
    public function detail($parameter)
    {
        return $this->repository->detail($parameter);
    }

    /**
     * 利用票券id取得使用紀錄
     * @param  $parameter
     * @return array
     */
    public function record($parameter)
    {
        return $this->repository->record($parameter);
    }

    /**
     * 轉贈票券
     * @param  $parameters
     */
    public function gift($parameters)
    {
        return $this->repository->gift($parameters);
    }

    /**
     * 轉贈票券退回
     * @param  $parameter
     */
    public function refund($parameter)
    {
        return $this->repository->refund($parameter);
    }


}