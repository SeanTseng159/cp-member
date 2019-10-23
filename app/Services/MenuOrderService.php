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
    public function getByCode($code)
    {
        return $this->repository->getByCode($code);
    }


}
