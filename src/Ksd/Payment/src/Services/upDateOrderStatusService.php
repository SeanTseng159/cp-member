<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;


use Ksd\Payment\Repositories\UpDateOrderStatusRepository;


class UpDateOrderStatusService
{
    protected $repository;


    public function __construct(UpDateOrderStatusRepository $repository)
    {
        $this->repository = $repository;

    }

    public function upDateOderByOrderNo($orderNo, $data=[])
    {
        $this->repository->upDateOderByOrderNo($orderNo, $data);
    }
    public function upDateOderDetailByOrderNo($orderNo, $data=[])
    {
        $this->repository->upDateOderDetailByOrderNo($orderNo, $data);
    }


}
