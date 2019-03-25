<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\Coupon;
use App\Repositories\BaseRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CouponRepository extends BaseRepository
{
    private $limit = 20;
    
    
    public function __construct(Coupon $model)
    {
        $this->model = $model;
    }
    
    /**
     * 取優惠卷之列表
     *
     * @param  $params
     *
     * @return mixed
     */
    public function list($params)
    {
        $modelSpecID = $params->modelSpecId;
        $modelType = $params->modelType;
    
        //使用對象之表格與欄位設定
        $clientItemTable = '';
    
    
        //todo select 動態欄位的方式
        switch ($modelType)
        {
            case 'diningCar':
                $clientItemTable = 'dining_cars';
                $clientItemColumns =['dining_cars.id','dining_cars.name'];
                $modelType = 'dining_car';
                break;
        }
        
        
    
        //取得該餐車所有的優惠卷
        $result = $this->model
            ->join($clientItemTable, 'coupons.model_spec_id', '=', "{$clientItemTable}.id")
            ->leftjoin('member_coupon', 'coupons.id', '=', "member_coupon.coupon_id")
            ->select(
                'dining_cars.id',
                'dining_cars.name',
                DB::raw('coupons.id AS couponID'),
                DB::raw('coupons.name AS couponTitle'),
                DB::raw('coupons.content as couponContent'),
                DB::raw('coupons.desc AS couponDesc'),
                DB::raw('coupons.qty as CouponQty'),
                DB::raw("CONCAT(DATE_FORMAT(coupons.start_at, '%Y-%m-%d'),' ~ ',DATE_FORMAT(coupons.expire_at, '%Y-%m-%d')) AS duration"),
                DB::raw('coupons.limit_qty as couponLimitQty'),
                DB::raw('COALESCE(SUM(member_coupon.count), 0) AS totalUsedCount')
                
                
            )
            ->where('coupons.model_type', $modelType)
            ->where('coupons.model_spec_id', $modelSpecID)
            ->where('coupons.on_sale_at', '<=', Carbon::now()->toDateTimeString())
            ->where('coupons.off_sale_at', '>=', Carbon::now()->toDateTimeString())
            ->where('coupons.status', true)
            ->groupBy('coupons.id')
            ->get();

        
        return $result;
    }
    
    /**
     * 取優惠卷的明細
     *
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        $result = $this->model
            ->leftjoin('member_coupon', 'coupons.id', '=', "member_coupon.coupon_id")
            ->select(
                DB::raw('coupons.id AS couponId'),
                DB::raw('coupons.name AS couponTitle'),
                DB::raw('coupons.content as couponContent'),
                DB::raw('coupons.desc AS couponDesc'),
                DB::raw('coupons.qty as CouponQty'),
                DB::raw("CONCAT(DATE_FORMAT(coupons.start_at, '%Y-%m-%d'),' ~ ',DATE_FORMAT(coupons.expire_at, '%Y-%m-%d')) AS duration"),
                DB::raw('coupons.limit_qty as couponLimitQty'),
                DB::raw('SUM(member_coupon.count) AS totalUsedCount'),
                DB::raw('coupons.start_at AS couponStartAt'),
                DB::raw('coupons.expire_at AS couponExpireAt'),
                'model_type',
                'model_spec_id'
            )
            ->where('coupons.id', $id)
            ->where('coupons.status', true)
            ->groupBy('coupons.id')
            ->first();
        
        return $result;

    }
    
    
}
