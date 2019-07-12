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

    public function __construct(PromoteGift $model)
    {
        $this->model = $model;
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

    public function addRecord($gifts = null, $id = null,$passiveMemberId = null)
    {
        \DB::connection('backend')->transaction(function () use (
                    $gifts,$id,$passiveMemberId
                )
        {
            $now = Carbon::now();
            //禮物紀錄
            foreach ($gifts as $key => $gift) {
            switch ($gift->send_condition) {
                //2:被邀請者 3:邀請者
                case '2':
                    $model = new PromoteGiftRecord;
                    $model->member_id = $id;
                    $model->inviter_member_id = $passiveMemberId;
                    $model->sent_at = $now;
                    $model->promote_gift_id = $gift->id;
                    $model->save();

                    //update已發送禮物
                    $this->model->where('id',$gift->id)
                    ->increment('used_qty',1);
                    break;
                case '3':
                    $model = new PromoteGiftRecord;
                    $model->member_id = $passiveMemberId;
                    $model->inviter_member_id = null;
                    $model->sent_at = $now;
                    $model->promote_gift_id = $gift->id;
                    $model->save();

                    //update已發送禮物
                    $this->model->where('id',$gift->id)
                    ->increment('used_qty',1);
                    break;
                default:
                    # code...
                    break;
                }
            }
        }); 
    }
}
