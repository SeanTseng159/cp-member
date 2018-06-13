<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\OrderRepository;

class OrderService extends BaseService
{
    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 根據 會員 id 取得訂單列表
     * @param $memberId
     * @param $startDate
     * @param $endDate
     * @return mixed
     */
    public function getMemberOrdersByDate($memberId = 0, $startDate = '', $endDate = '')
    {
        return $this->repository->getMemberOrdersByDate($memberId, $startDate, $endDate);
    }
}
