<?php
/**
 * User: Danny
 * Date: 2019/07/11
 * Time: 上午 9:42
 */

namespace App\Repositories\Ticket;

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
}
