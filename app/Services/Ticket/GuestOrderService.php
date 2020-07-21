<?php
/**
 * User: lee
 * Date: 2020/07/12
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\GuestOrderRepository;

class GuestOrderService extends BaseService
{
    public function __construct(GuestOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 訂單搜尋
     * @param $params
     * @return mixed
     */
    public function findByPhone($params)
    {
        return $this->repository->findByPhone($params);
    }
}
