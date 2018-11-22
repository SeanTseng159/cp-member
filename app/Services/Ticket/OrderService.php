<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\OrderRepository;
use App\Repositories\Ticket\OrderDetailRepository;

class OrderService extends BaseService
{
    protected $orderDetailRepository;

    public function __construct(OrderRepository $repository, OrderDetailRepository $orderDetailRepository)
    {
        $this->repository = $repository;
        $this->orderDetailRepository = $orderDetailRepository;
    }

    /**
     * 根據 No 找單一訂單
     * @param $orderNo
     * @return mixed
     */
    public function findByOrderNo($orderNo)
    {
        return $this->repository->findByOrderNo($orderNo);
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

    /**
     * 取會員某商品購買數
     * @param $productId
     * @param $memberId
     * @return mixed
     */
    public function getCountByProdAndMember($productId = 0, $memberId = 0)
    {
        return $this->orderDetailRepository->getCountByProdAndMember($productId, $memberId);
    }
}
