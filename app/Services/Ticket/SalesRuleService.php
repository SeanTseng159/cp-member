<?php

namespace App\Services\Ticket;

use App\Repositories\DiscountCodeRepository;
use App\Repositories\OrderDiscountRepository;

class SalesRuleService
{
    private $repository;

    private $order_discount_repository;

    public function __construct(DiscountCodeRepository $repository, OrderDiscountRepository $orderDiscountRepository)
    {
        $this->repository = $repository;
        $this->order_discount_repository = $orderDiscountRepository;
    }

    public function getEnableDiscountByCode($code)
    {
        return $this->repository->getEnableDiscountCode($code);
    }

    public function checkCodeDiscount($discount, $memberId)
    {
        if (empty($discount)) return false;
        // 超過優惠折扣碼個人可使用次數
        $userUsed  = $this->order_discount_repository->getMemberUsedCount($discount->discount_code_id, 1, $memberId);
        if ( $userUsed >= (int)$discount->discount_code_member_use_count) {
            return false;
        }else
        {
            return true;
        }
    }

}