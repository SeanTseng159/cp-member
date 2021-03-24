<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\Coupon;
use App\Models\MemberCoupon;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CouponRepository extends BaseRepository
{
    private $limit = 20;
    
    
    public function __construct(Coupon $model, MemberCoupon $memberCouponModel)
    {
        $this->model = $model;
        $this->memberCouponModel = $memberCouponModel;
    }

    /**
     * 取優惠卷之列表
     *
     * @param $modelSpecID
     * @param $modelType
     * @return mixed
     */
    public function list($modelSpecID,$modelType)
    {
//        $modelSpecID = $params->modelSpecId;
//        $modelType = $params->modelType;
    
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
     * 取得會員領取店家優惠 可使用
     * @param $memberID
     * @return mixed
     */
    public function memberCurrentCouponlist($memberID) 
    {
        // $modelType = 'dining_car';

        //取得該會員的優惠卷
        $result = $this->model
        ->join('member_coupon', 'coupons.id', '=', "member_coupon.coupon_id")
        ->select(
            // 'dining_cars.id',
            // 'dining_cars.name',
            DB::raw('coupons.id'),
            DB::raw('coupons.name'),
            DB::raw('coupons.content'),
            DB::raw('coupons.desc'),
            DB::raw('coupons.qty'),
            DB::raw('coupons.limit_qty'),
            DB::raw("DATE_FORMAT(coupons.expire_at, '%Y-%m-%d') AS endtime"),
            DB::raw('member_coupon.count')
        )
        ->where('member_coupon.member_id', $memberID)
        // ->where('coupons.model_type', $modelType)
        ->where('coupons.limit_qty', '>', 'member_coupon.count')
        ->where('coupons.online_or_offline', 2)
        ->where('coupons.status', true)
        // ->groupBy('coupons.id')
        ->get();

        return $result;
    }

    /**
     * 取得會員領取店家優惠 已使用
     * @param $memberID
     * @return mixed
     */
    public function memberUsedCouponlist($memberID) 
    {
        // $modelType = 'dining_car';

        //取得該會員的優惠卷
        $result = $this->model
        ->join('member_coupon', 'coupons.id', '=', "member_coupon.coupon_id")
        ->select(
            // 'dining_cars.id',
            // 'dining_cars.name',
            DB::raw('coupons.id'),
            DB::raw('coupons.name'),
            DB::raw('coupons.content'),
            DB::raw('coupons.desc'),
            DB::raw('coupons.qty'),
            DB::raw('coupons.limit_qty'),
            DB::raw("DATE_FORMAT(member_coupon.created_at, '%Y-%m-%d') AS endtime"),
            DB::raw('member_coupon.count')

        )
        ->where('member_coupon.member_id', $memberID)
        // ->where('coupons.model_type', $modelType)
        ->where('member_coupon.count', '!=', 0)
        ->where('coupons.limit_qty', '>=', 'member_coupon.count')
        ->where('coupons.online_or_offline', 2)
        ->where('coupons.status', true)
        // ->groupBy('coupons.id')
        ->get();

        return $result;
    }

    /**
     * 取得會員領取店家優惠 已失效
     * @param $memberID
     * @return mixed
     */
    public function memberDisabledCouponlist($memberID) 
    {
        // $modelType = 'dining_car';

        //取得該會員的優惠卷
        $result = $this->model
        ->join('member_coupon', 'coupons.id', '=', "member_coupon.coupon_id")
        ->select(
            // 'dining_cars.id',
            // 'dining_cars.name',
            DB::raw('coupons.id'),
            DB::raw('coupons.name'),
            DB::raw('coupons.content'),
            DB::raw('coupons.desc'),
            DB::raw('coupons.qty'),
            DB::raw('coupons.limit_qty'),
            DB::raw("DATE_FORMAT(coupons.off_sale_at, '%Y-%m-%d') AS endtime"),
            DB::raw('member_coupon.count')
        )
        ->where('member_coupon.member_id', $memberID)
        // ->where('coupons.model_type', $modelType)
        ->where('coupons.online_or_offline', 2)
        ->where(function ($query) {
            $query->orWhere('coupons.qty', '<=', 'member_coupon.count')
                  ->where('coupons.expire_at', '>=', Carbon::today()->subMonths(1))
                  ->where('coupons.expire_at', '<=', Carbon::today())
                  ->orWhere('coupons.status', 0);
        })
        ->where(function ($query) {
            $query->orWhere('coupons.qty', '<=', 'member_coupon.count')
                  ->orWhere('coupons.status', 0);
        })
        // ->groupBy('coupons.id')
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

    /**
     * 會員領取店家優惠券
     * @param $data
     * @return mixed
     */
    public function createAndCheck($data)
    {
        $check = $this->memberCouponModel->where('coupon_id', $data['coupon_id'])->where('member_id', $data['member_id'])->first();
        if (empty($check)) {
            $coupon = new MemberCoupon;
            $coupon->member_id = $data['member_id'];
            $coupon->coupon_id = $data['coupon_id'];
            $coupon->is_collected = $data['is_collected'];
            $coupon->count = $data['count'];
            $coupon->save();
            // $this->memberCouponModel->create($data);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 依據優惠卷編號，查詢coupon資料
     * @param $code
     * @return mixed
     */
    public function getEnableCouponByCode($code)
    {
        $date = date('Y-m-d H:i:s');
        return $this->model
            ->where('online_code_value', $code)
            ->where('status',1)
            ->where('start_at', '<=', $date)
            ->where('expire_at', '>', $date)
            ->where('qty','!=', 0)
            ->first();
    }

    public function availableCoupons($memberId)
    {
        return $this->repository->availableCoupons($memberId);
    }
    

    //取得優惠卷倒數過期前7天前資料
    public function findCouponEndTime()
    {
        $today = Carbon::today();
        $a1= Carbon::today('Asia/Taipei')->addDay(8);
        $a2= Carbon::today('Asia/Taipei')->addDay(7);

        $data=$this->model->where('status', '1')
        ->where('expire_at', '<',$a1)
        ->where('expire_at', '>=',$a2)
        ->where('qty','>',0)->get();        
      return $data;     
    } 
}
