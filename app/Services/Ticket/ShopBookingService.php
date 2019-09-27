<?php

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ShopBookingRepository;

class ShopBookingService extends BaseService
{
    protected $repository;

    public function __construct(ShopBookingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function findBookingLimit($id)
    {
        return $this->repository->findBookingLimit($id);
    }
}
