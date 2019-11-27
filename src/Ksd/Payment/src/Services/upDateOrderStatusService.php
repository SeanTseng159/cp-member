<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;


use Ksd\Mediation\Repositories\upDateOrderStatusRepository;
use Ksd\Mediation\Config\ProjectConfig;
use Log;

class upDateOrderStatusService
{
    protected $repository;


    public function __construct( upDateOrderStatusRepository $repository)
    {
        $this->repository = $repository;

    }

    public function upDateOderByOrderNo($orderNo, $data)
    {
        $this->repository->upDateOderByOrderNo($orderNo, $data);
    }
    public function upDateOderDetailByOrderNo($orderNo, $data)
    {
        $this->repository->upDateOderDetailByOrderNo($orderNo, $data);
    }


}
