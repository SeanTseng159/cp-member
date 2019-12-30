<?php
/**
 * Created by Fish.
 * 2019/12/19 5:50 下午
 */

namespace App\Repositories;

use App\Models\DiningCarDiscount;
use Carbon\Carbon;
use DB;

class DiningCarDiscountRepositories extends BaseRepository
{
    protected $model;

    public function __construct(DiningCarDiscount $model)
    {
        $this->model = $model;
    }

    public function find($id)
    {
        $now = Carbon::now();

        return $this->model
            ->where('id', $id)
            ->where('start_at','<=', $now)
            ->where('end_at', '>',$now)
            ->where('status',1)
            ->first();
    }//end find
    
    public function checkCount($id)
    {
        $count=$this->model
            ->join('member_dining_car_discount', 'member_dining_car_discount.discount_id', '=', "dining_car_discount.id")
            ->select(DB::raw('COUNT(member_dining_car_discount.id) AS COUNT'))
            ->where('dining_car_discount.id',$id)
            ->first();
        $number=$this->model
            ->select('number')
            ->where('id',$id)
            ->first();
        return ['count'=>$count,'number'=>$number];
    }   
}