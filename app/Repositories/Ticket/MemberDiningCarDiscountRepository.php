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
        $result = $this->model
            ->where('member_id',$memberId)
            ->when($type,
                function ($query) use ($type) {
                    //禮物未使用
                    if ($type === 1) {
                        $query->whereNull('used_time');
                    }elseif($type === 2){
                        $query->whereNotNull('used_time');
                    }
                    return $query;
                })
            ->whereHas('discount',
                function ($q) use ($type) {
                    //取得折價卷的相關資料
                    if ($type ===1) {
                        $q->where('start_at','<=',Carbon::today())
                        ->where('end_at','>=',Carbon::today())
                        ->where('status',1);
                    }elseif($type ===2) {
                        $q->where('end_at','<=',Carbon::today())
                        ->where('status',0);
                    }

                    return $q;
                })
            ->with('discount')
            ->get();
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

    public function availableDiscount($memberId)
    {
        $favoriteList = $this->list($memberId,1);
        return $favoriteList->count();
    }
  
}
