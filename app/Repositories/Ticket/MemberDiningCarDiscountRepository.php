<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;


use App\Enum\ClientType;
use App\Enum\GiftType;
use App\Models\MemberDiningCarDiscount;

use App\Repositories\BaseRepository;
use App\Services\ImageService;
use Carbon\Carbon;
use DB;
use App\Core\Logger;
use function foo\func;


class MemberDiningCarDiscountRepository extends BaseRepository
{
    private $model;

    public function __construct(MemberDiningCarDiscount $model)
    {
        $this->model=$model;
        
    }




    /** 取得
     *
     * @param        $type :1:可使用/2:已使用or過期
     * @param        $memberId
     *
     *
     * @return mixed
     */
    public function list($type, $memberId)
    {

        //會員的折價卷
        if($type==1){
            $result = $this->model
            ->where('member_id',$memberId)
            ->whereNull('used_time')
            ->whereHas('discount',
                function ($q) {
                    //取得折價卷的相關資料
                    $q->where('on_start_at','<=',Carbon::today())
                        ->where('on_end_at','>=',Carbon::today())
                        ->where('status',1);
                    return $q;
                })
            ->with('discount')
            ->get();    
        }elseif($type==2){
            $used = $this->model
                ->where('member_id',$memberId)
                ->whereNotNull('used_time')
                ->get();
            $expired=$this->model
                ->where('member_id',$memberId)
                ->whereHas('discount',
                    function ($q) {
                        //取得折價卷的相關資料
                        $q->where('end_at','<',Carbon::today());
                        return $q;
                    })
                ->with('discount')
                ->get();
            
            $result = $used->merge($expired);
        }
        return $result;
    }


    public function find($id, $memberId)
    {

        return $this->model
            ->with('discount')
            ->where('member_id', $memberId)
            ->where('id', $id)
            ->first();

    }
    public function checkOnlyOne($discountId, $memberId)
    {

        return $this->model
            ->where('member_id', $memberId)
            ->where('discount_id', $discountId)
            ->first();

    }

    public function availableDiscount($memberId)
    {
        $favoriteList = $this->list(1,$memberId);
        return $favoriteList->count();
    }

    public function createQrcode($memberId,$discountID,$code)
    {
        return $this->model
                    ->insert(['discount_id' => $discountID,
                            'member_id' => $memberId,
                            'qrcode' => $code,
                            'created_at'=>Carbon::now()]);
                    

    }
  
}
