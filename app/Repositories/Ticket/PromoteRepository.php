<?php
/**
 * User: Danny
 * Date: 2019/07/11
 * Time: 上午 9:42
 */

namespace App\Repositories\Ticket;

use App\Enum\ClientType;
use Illuminate\Database\QueryException;
use App\Repositories\BaseRepository;
use App\Models\PromoteGift;
use App\Models\PromoteGiftRecord;
use Carbon\Carbon;

class PromoteRepository extends BaseRepository
{
    protected $model;

    public function __construct(PromoteGift $model, PromoteGiftRecord $PromoteGiftRecordModel)
    {
        $this->model = $model;
        $this->PromoteGiftRecordModel = $PromoteGiftRecordModel;
    }

    public function allPromoteGift()
    {
        $now = Carbon::now();

        return $this->model->where('activity_start_at', '<=', $now)
            ->where('activity_end_at', '>=', $now)
            ->where('usage_start_at', '<=', $now)
            ->where('usage_end_at', '>=', $now)
            ->where('status',2)
            ->WhereColumn('stock_qty','>','used_qty')
            ->get();
    }

    public function findPromoteGift()
    {
        $now = Carbon::now();   
        return $this->model->where('activity_start_at', '<=', $now)
            ->where('activity_end_at', '>=', $now)
            ->where('usage_start_at', '<=', $now)
            ->where('usage_end_at', '>=', $now)
            ->where('status',2)
            ->WhereColumn('stock_qty','>','used_qty')
            ->where('send_condition',3)
            ->get();
    }

    public function addRecord($gifts = null, $MemberId = null,$passiveMemberId = null)
    {
        \DB::connection('backend')->transaction(function () use (
                    $gifts,$MemberId,$passiveMemberId
                )
        {
            $now = Carbon::now();
            //禮物紀錄
            foreach ($gifts as $key => $gift) {
                $model = new PromoteGiftRecord;
                $model->sent_at = $now;
                $model->promote_gift_id = $gift->id;
            switch ($gift->send_condition) {
                //2:被邀請者 3:邀請者
                case '2':
                    $model->member_id = $MemberId;
                    $model->inviter_member_id = $passiveMemberId;
                    break;
                case '3':
                    $model->member_id = $passiveMemberId;
                    $model->inviter_member_id = null;
                    break;
                default:
                    # code...
                    break;
                }
                $model->save();
                //update已發送禮物
                $this->model->where('id',$gift->id)
                ->increment('used_qty',1);
            }
        }); 
    }

    public function friendValue($memberId)
    {
        return $this->PromoteGiftRecordModel->where('inviter_member_id', $memberId)
            ->distinct('member_id')
            ->count('member_id');
    }

    public function invitationCheck($passiveMemberId)
    {
        $check = $this->PromoteGiftRecordModel->where('member_id', $passiveMemberId)
            ->count('inviter_member_id');
        if($check==0){
            return true; 
        }else
        {
            return false; 
        }
    }

    /** 取得使用者之禮物列表，如果$client與$clientID非null，則取得該餐車的資料即可
     *
     * @param        $type :1:可使用/2:已使用or過期
     * @param        $memberId
     *
     * @param        $client
     * @param        $clientId
     *
     * @return mixed
     */
    public function list($type, $memberId, $client, $clientId)
    {
        if($type == 1) {
            $result = $this->PromoteGiftRecordModel
                ->whereMemberId($memberId)
                ->when($type,
                    function ($query) use ($type) {
                        $query->whereNull('verifier_at');
                        return $query;
                    })
                ->whereHas('promoteGift',
                    function ($query) use ($type) {
                        $now = Carbon::now();
                        //獎品未使用
                        $query->where('usage_start_at', '<=', $now)->where('usage_end_at', '>', $now);
                        $query->where('status', 2);
                    })
                ->with(['promoteGift', 'promoteGift.image'])
                ->get();
        }

        if ($type == 2) {
            $used = $this->PromoteGiftRecordModel
                ->whereMemberId($memberId)
                ->whereNotNull('verifier_at')
                ->whereHas('promoteGift',
                    function ($query) use ($type) {
                        $query->where('status', 2);
                    })
                ->with(['promoteGift', 'promoteGift.image'])
                ->get();
            $expired = $this->PromoteGiftRecordModel
                ->whereMemberId($memberId)
                ->whereHas('promoteGift',
                    function ($query) use ($type) {
                        $now = Carbon::now();
                        $query->where('usage_end_at', '<', $now);
                        $query->where('status', 2);
                    })
                ->with(['promoteGift', 'promoteGift.image'])
                ->get();
            $result = $used->merge($expired)->unique();
        }
        return $result;
    }

    public function findPromoteGiftRecord($id, $memberId)
    {
        return $this->PromoteGiftRecordModel
                    ->whereMemberId($memberId)
                    ->whereId($id)
                    ->first();
    }

    public function availablePromoteGift($memberId)
    {
        return $this->PromoteGiftRecordModel
                    ->whereNull('verifier_at')
                    ->whereHas('promoteGift', function ($query) {
                        $now = Carbon::now();
                        $query->where('usage_start_at', '<=', $now)->where('usage_end_at', '>', $now)->where('status', 2);
                    })
                    ->whereMemberId($memberId)
                    ->count();
    }
}
