<?php
/**
 * User: Lee
 * Date: 2018/01/14
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Services;

use Ksd\IPassPay\Repositories\IpasspayLogRepository;

class IpasspayLogService
{
    protected $repository;

    public function __construct(IpasspayLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create($data)
    {
        return $this->repository->create($data);
    }

    public function update($orderId, $data)
    {
        return $this->repository->update($orderId, $data);
    }

    public function queryOnlyOrderId($data, $datetime)
    {
        return $this->repository->queryOnlyOrderId($data, $datetime);
    }
}
