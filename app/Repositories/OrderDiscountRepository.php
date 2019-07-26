<?php
namespace App\Repositories;

use App\Models\OrderDiscount;
use App\Models\Ticket\DiscountCode;
use App\Models\Ticket\DiscountCodeBlockProd;
use App\Models\Ticket\Order;


class OrderDiscountRepository
{
    protected $model;
    protected $discountCodeModel;
    protected $discountCodeBlockProdModel;
    protected $orderModel;

    public function __construct(OrderDiscount $orderDiscount, 
                                DiscountCode $discountCodeModel,
                                DiscountCodeBlockProd $discountCodeBlockProdModel,
                                Order $orderModel)
    {
        $this->model = $orderDiscount;
        $this->discountCodeModel = $discountCodeModel;
        $this->discountCodeBlockProdModel = $discountCodeBlockProdModel;
        $this->orderModel = $orderModel;
    }

    public function getMemberUsedCount($discountId, $type, $memberId)
    {
        return $this->model->where('discount_id', $discountId)
                           ->where('discount_type', $type)
                           ->whereHas('order', function($q) use ($memberId){
                               $q->where('member_id', $memberId);
                           })
                           ->count();
    }

    public function tagCheck($discountCodeId, $prodId)
    {
        $count = $this->discountCodeModel->join('discount_code_tags as dct', 'discount_codes.discount_code_id', 'dct.discount_code_id')
                                      ->join('tag_prods as tp', 'dct.tag_id', 'tp.tag_id')
                                      ->where('discount_codes.discount_code_id' , $discountCodeId)
                                      ->where('dct.deleted_at' ,0)
                                      ->where('tp.prod_id' , $prodId) 
                                      ->count();
        $check = ($count>0) ? true:false;
        return $check;
    }

    public function prodCheck($discountCodeId, $prodId)
    {
        $count = $this->discountCodeBlockProdModel->where('discount_code_id' ,$discountCodeId)
                                                  ->where('prod_id' ,$prodId)
                                                  ->count();

        $check = ($count==0) ? true:false;
        return $check;
    }

    public function firstBuyCheck($memberId)
    {
        $count = $this->orderModel->where('member_id' ,$memberId)
                                  ->count();

        $check = ($count==0) ? true:false;
        return $check;
    }
}
