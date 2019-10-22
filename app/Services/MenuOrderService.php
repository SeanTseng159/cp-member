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
        $this->repository->create($shopId, $menu, $payment, $cellphone, $time, $remark, $memberId);
    }
}
