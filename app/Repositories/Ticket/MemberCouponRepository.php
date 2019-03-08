<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Models\Coupon;
use App\Models\MemberCoupon;
use App\Models\MemberCouponItem;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MemberCouponRepository extends BaseRepository
{
    private $limit = 20;
    
    public function __construct(MemberCoupon $model)
    {
        $this->model = $model;
    }
    
    /** 取得使用者之優惠劵列表，若$couponId 有值，則取得該coupon資料
     * @param      $memberID
     * @param null $couponID
     *
     * @return mixed
     */
    public function list($memberID,$couponID = null)
    {
        
        
        return $this->model
            ->select('coupon_id','is_collected','count')
            ->where('member_id', $memberID)
            ->when($couponID, function ($query) use ($couponID) {
                $query->where('coupon_id',$couponID);
            })
            ->get();
    }
    
    /** 取得使用者之優惠劵列表與優惠卷詳細資訊
     *
     * @param      $memberID
     *
     * @param      $status
     *                      current 未使用 1
     *                      used    已使用 2
     *                      expired 已失效 3
 *
     * @return mixed
     */
    public function favoriteCouponList($memberID,$status)
    {
        
        $result = $this->model
            ->join('coupons', 'coupons.id', '=', "coupon_id")
            ->select(
                'coupons.id',
                DB::raw('coupons.name AS title'),
                'content',
                DB::raw("CONCAT(DATE_FORMAT(start_at, '%Y-%m-%e'),' ~ ',DATE_FORMAT(expire_at, '%Y-%m-%e')) AS duration"),
                'model_type',
                'model_spec_id'
                
            )
            ->where('member_id',$memberID)
            ->where('status',1)
            ->where('is_collected',1)
            ->when($status,
                function ($query) use ($status) {
                    if ($status === 1)
                    {
                        $query->where('count', 0)
                            ->where('coupons.start_at', '<=', Carbon::now()->toDateTimeString())
                            ->where('coupons.expire_at', '>=', Carbon::now()->toDateTimeString());
                    }
                    elseif ($status === 2)
                    {
                        $query->where('count', '>', 0)
                            ->where('coupons.start_at', '<=', Carbon::now()->toDateTimeString())
                            ->where('coupons.expire_at', '>=', Carbon::now()->toDateTimeString());
                    }
                    elseif ($status === 3)
                    {
                        $query->where('coupons.expire_at', '<=', Carbon::now()->toDateTimeString());
                    }
            
                })
            ->orderBy('expire_at','asc')
            ->orderBy('member_coupon.updated_at','asc')
            ->get();
        
        //與client 端(ex.餐車)對應
        $classType = ['dining_car' => 'App\Models\Ticket\DiningCar'];
        
        $modelTypes = $result->pluck('model_type');
        
        
        //取得所有需要對應的表與資料(ex.餐車)
        $tables = [];
        
        foreach ($modelTypes as $modelType)
        {
            $model = new $classType[$modelType]();
            $tables[$modelType] = $model->all();
        }
        
        
        foreach ($result as $item)
        {
            $clientInfo =$tables[$item['model_type']]->where('id', $item->model_spec_id)->first();
            $item->Name = $clientInfo->name;
        }
    
        $collection = $result->map(function ($item) {
            unset($item->model_type);
            unset($item->model_spec_id);
            return $item;
        });
        
        return $collection;
    }



    
    /**
     *  優惠卷加入使用者的收藏
     *
     * @param $memberId
     * @param $couponID
     *
     * @return MemberCoupon
     */
    
    public function add($memberId,$couponID)
    {
        $model = new MemberCoupon();
        
        $model->member_id = $memberId;
        $model->coupon_id = $couponID;
        $model->count = 0 ;
        $model->is_collected = 1 ;
        $model->save();
    
        return $model;
    
    }
    
    
    /**
     *  優惠卷從使用者的收藏移除
     *
     * @param $memberId
     * @param $couponID
     *
     * @param $isFavorite
     *
     * @return MemberCoupon
     */
    
    public function update($memberId,$couponID,$isFavorite)
    {
        return $this->model
            ->where('member_id', $memberId)
            ->where('coupon_id', $couponID)
            ->update(['is_collected' => $isFavorite]);
        
    }
    
    
    /**
     * 使用優惠卷
     *
     * @param $memberId
     * @param $couponID
     *
     * @return mixed  obj.status :
     *                   0:可使用優惠券(沒用過或還可使用)
     *                   1:已使用(已達自己可使用上限，不能再使用)
     *                   2:優惠卷已兌換完畢(已達餐車設定總張數)
    */
    
    public function use($memberId, $couponID)
    {
        try
        {
            //回傳物件
            $returnObj = new \stdClass();
            $returnObj->status = 0;
            $returnObj->used = false;
            
            $memberCoupon = $this->model
                ->where('member_id', $memberId)
                ->where('coupon_id', $couponID)
                ->first();
            
            
    
            $coupon = Coupon::where('id', $couponID)->first();
            if(!$coupon)
            {
                return false;
            }
            
            
            if (!Carbon::now()->between(Carbon::parse($coupon->start_at), Carbon::parse($coupon->expire_at)))
            {
                $returnObj->status = 3;
                return $returnObj;
            }
            
            
    
          
            
            //檢查是否逾期
            
            //檢查是否超過總數
            $max = $coupon->qty;
            $currentUsed = $this->model->where('coupon_id', $couponID)->sum('count');
            if ($currentUsed >= $max)
            {
                $returnObj->status = 2;
                return $returnObj;
            }
            
            DB::beginTransaction();
        
            //不在收藏列表
            if (!$memberCoupon)
            {
                $memberCoupon = new MemberCoupon();
                $memberCoupon->member_id = $memberId;
                $memberCoupon->coupon_id = $couponID;
                $memberCoupon->count = 1;
                $memberCoupon->save();
            }
            else
            {
                //取得coupon的限制張數，是否已達限制
                if ($memberCoupon->count >= $coupon->limit_qty)
                {
                    $returnObj->status = 1;
                    return $returnObj;
                }
                $memberCoupon->count++;
                $memberCoupon->save();
            }
        
            //確定已使用
            $memberCouponItem = new MemberCouponItem();
            $memberCouponItem->member_coupon_id = $memberCoupon->id;
            $memberCouponItem->number = $memberCoupon->count;
            $memberCouponItem->used_time = Carbon::now();
            $memberCouponItem->save();
    
            $returnObj->used = true;
            
            //回傳是否還可以使用
            if ($memberCoupon->count >= $coupon->limit_qty)
            {
                $returnObj->status = 1;
            }
            
            //是否超過最大張數
            if ($currentUsed >= $max - 1)
            {
                $returnObj->status = 2;
            }
            DB::commit();
            
            return $returnObj;
        
        }
        catch (\Exception $e)
        {
           
            DB::rollBack();
            
            var_dump($e);

            return false;
        }

    }
    
}