<?php
/**
 * User: lee
 * Date: 2018/12/05
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\UberCouponRepository;

class UberCouponService extends BaseService
{
    public function __construct(UberCouponRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取資料
     * @return mixed
     */
    public function findByOrderDetailId($order_detail_id = 0)
    {
        return $this->repository->findByOrderDetailId($order_detail_id);
    }
}
