<?php

namespace App\Services;


use App\Repositories\MenuOrderRepository;

class MenuOrderService extends BaseService
{
    /**
     * Default repository.
     *
     * @var string
     */
    protected $repository;

    public function __construct(MenuOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create($shopId, $menu, $payment, $cellphone, $time, $remark, $memberId = null)
    {
        return $this->repository->create($shopId, $menu, $payment, $cellphone, $time, $remark, $memberId);
    }

    public function get($menuOrderId)
    {
        return $this->repository->get($menuOrderId);
    }
    public function getByOrderNo($menuOrderNo)
    {
        return $this->repository->getByOrderNo($menuOrderNo);
    }

    public function getByCode($code)
    {
        return $this->repository->getByCode($code);
    }

    public function updateStatus($code, $status = false)
    {
        return $this->repository->updateStatus($code, $status);
    }

    public function memberList($memberId)
    {
        return $this->repository->memberList($memberId);
    }

}
