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
use App\Repositories\Ticket\MenuRepository;

class OrderService extends BaseService
{
    protected $orderDetailRepository;
    protected $menuRepository;

    public function __construct(OrderRepository $repository, OrderDetailRepository $orderDetailRepository, MenuRepository $menuRepository)
    {
        $this->repository = $repository;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->menuRepository = $menuRepository;
    }

    /**
     * 成立訂單
     * @param $params
     * @param $cart
     * @return mixed
     * @throws \App\Exceptions\CustomException
     */
    public function create($params, $cart)
    {
        return $this->repository->create($params, $cart);
    }

    /** 根據點餐單成立訂單
     * @param $params
     * @param $menuOrder
     * @return
     */
    public function createByMenuOrder($params,$menuOrder)
    {
        return $this->repository->createByMenuOrder($params, $menuOrder);
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
     * 根據 No 找單一訪客訂單
     * @param $orderNo
     * @return mixed
     */
    public function findByOrderNoWithGuestOrder($orderNo, $isDetail = true)
    {
        return $this->repository->findByOrderNoWithGuestOrder($orderNo, $isDetail);
    }

    /**
     * 根據 No 找單一訂單 [未失效]
     * @param $memberId
     * @param $orderNo
     * @return mixed
     */
    public function findCanShowByOrderNo($memberId = 0, $orderNo = 0)
    {
        return $this->repository->findCanShowByOrderNo($memberId, $orderNo);
    }

    /**
     * 根據 No 找可付款訂單
     * @param $orderNo
     * @return mixed
     */
    public function findCanPay($orderNo = 0)
    {
        return $this->repository->findCanPay($orderNo);
    }

    /**
     * 根據 No 找單一訂單的詳細資料
     * @param $orderNo
     * @return mixed
     */
    public function findByOrderNoWithDetail($orderNo = 0)
    {
        return $this->repository->findByOrderNoWithDetail($orderNo);
    }

    /**
     * 根據 會員 id 取得訂單列表
     * @param $params [memberId, startDate, endDate, status, orderNo]
     * @return mixed
     */
    public function getMemberOrdersByDate($params)
    {
        return $this->repository->getMemberOrdersByDate($params);
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

    /**
     * 依據訂單編號 更新 信用卡
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateCC($orderNo, $data = [])
    {
        return $this->repository->updateCC($orderNo, $data);
    }

    /**
     * 依據訂單編號 更新
     * @param $id
     * @param $params
     * @return mixed
     */
    public function updateForRepay($orderNo, $params = [])
    {
        return $this->repository->updateForRepay($orderNo, $params);
    }

    /**
     * 取前一小時有付款的餐車訂單
     * @return mixed
     */
    public function getOneHourAgoPaidDiningCarOrders()
    {
        return $this->repository->getOneHourAgoPaidDiningCarOrders();
    }

    /**
     * 依據訂單編號 更新
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateByOrderNo($orderNo, $data = [])
    {
        return $this->repository->updateByOrderNo($orderNo, $data);
    }

}
