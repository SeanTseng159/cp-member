<?php
/**
 * User: lee
 * Date: 2020/07/10
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ShippingFeeDetailRepository;

class ShippingFeeDetailService extends BaseService
{
    protected $repository;

    public function __construct(ShippingFeeDetailRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 依供應商取運費
     * @param $supplierId
     * @return mixed
     */
    public function findBySupplierId($supplierId)
    {
        return $this->repository->findBySupplierId($supplierId);
    }
}
