<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\OrderRefundRepository;

class OrderRefundService extends BaseService
{
    public function __construct(OrderRefundRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 根據 Id 找單一退訂單
     * @param $id
     * @return mixed
     */
    public function find($id = 0)
    {
        return $this->repository->find($id);
    }
}
