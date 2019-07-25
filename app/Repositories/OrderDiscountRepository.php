<?php
namespace App\Repositories;

use App\Models\OrderDiscount;

class OrderDiscountRepository
{
    protected $model;

    public function __construct(OrderDiscount $orderDiscount)
    {
        $this->model = $orderDiscount;
    }

    public function getMemberUsedCount($discountId, $type, $memberId)
    {
        return $this->model->where('order_discount_id', $discountId)
                           ->where('discount_type', $type)
                           ->whereHas('order', function($q) use ($memberId){
                               $q->where('member_id', $memberId);
                           })
                           ->count();
    }
}
