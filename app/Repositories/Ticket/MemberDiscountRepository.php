<?php

/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;




use App\Repositories\BaseRepository;

use Carbon\Carbon;
use DB;
use App\Core\Logger;

use  App\Models\Ticket\MemberDiscount;
use Log;

class MemberDiscountRepository extends BaseRepository
{
    private $model;

    public function __construct(MemberDiscount $model)
    {
        $this->model = $model;
    }

    public function listCanUsed($memberID)
    {
        return $this->model->with([
            'discountCode',
            'discountCodeBlock',
            'discountCodeTag',
            'discountCodeTag.tagProdId',
            'discountCodeMember'
        ])
            ->whereHas('discountCode', function ($query) {
                $query->where('discount_code_status', 1)
                    ->where('deleted_at', 0)
                    ->where('discount_code_starttime', '<=', Carbon::today())
                    ->where('discount_code_endtime', '>', Carbon::today());
            })
            ->where('member_id', $memberID)
            ->where('status', 1)
            ->where('used', 0)
            ->get();
    }


    public function listProdDiscount($memberID)
    {
        return $this->model->where('member_id', $memberID)
            ->get();
    }


    public function createAndCheck($data)
    {
        $check = $this->model->where('discount_code_id', $data['discount_code_id'])->where('member_id', $data['member_id'])->first();
        if (empty($check)) {
            $this->model->create($data);
            return true;
        } else {
            return false;
        }
    }

    //取得有效票
    public function current($memberID)
    {
        return $this->model->with([
            'discountCode',
            'discountCode.discountCodeTag.tag',
            'discountCode.discountCodeMember' => function ($query) use ($memberID) {
                $query->where('member_id', $memberID);
            }
        ])
            ->whereHas('discountCode', function ($query) {
                $query->where('discount_code_status', 1)
                    ->where('deleted_at', 0)
                    ->where('discount_code_starttime', '<=', Carbon::today())
                    ->where('discount_code_endtime', '>', Carbon::today())
                    ->whereColumn('discount_code_limit_count', '>', 'discount_code_used_count');
            })
            ->where('member_id', $memberID)
            ->where('status', 1)
            ->where('used', 0)
            ->get();
    }

    //取得失效
    public function disabled($memberID)
    {
        // 票過期，使用數量達上限，票後台設定關閉
        $data2 = $this->model->with(['discountCode', 'discountCode.discountCodeTag.tag',])
            ->orWhereHas('discountCode', function ($query) {
                // 軟刪除
                $query->where('deleted_at', 1)
                    ->where('discount_code_endtime', '>=', Carbon::today()->subMonths(1))
                    ->where('discount_code_endtime', '<=', Carbon::today());
            })
            ->orWhereHas('discountCode', function ($query) {
                // 關閉
                $query->where('discount_code_status', 0)
                    ->where('discount_code_endtime', '>=', Carbon::today()->subMonths(1))
                    ->where('discount_code_endtime', '<=', Carbon::today());
            })
            ->orWhereHas('discountCode', function ($query) {
                // 使用數量達上限
                $query->where('discount_code_status', 1)
                    ->where('deleted_at', 0)
                    ->whereColumn('discount_code_limit_count', '<=', 'discount_code_used_count')
                    ->where('discount_code_endtime', '>=', Carbon::today()->subMonths(1))
                    ->where('discount_code_endtime', '<=', Carbon::today());
            })
            ->orWhereHas('discountCode', function ($query) {
                // 票過期
                $query->where('discount_code_status', 1)
                    ->where('deleted_at', 0)
                    ->where('discount_code_endtime', '>=', Carbon::today()->subMonths(1))
                    ->where('discount_code_endtime', '<=', Carbon::today());
            })
            ->where('member_id', $memberID)
            ->where('used', 0)
            ->where('status', 1)
            ->get();

        return collect($data2);
    }


    public function used($memberID)
    {

        return $this->model->with([
            'discountCode',
            'discountCode.discountCodeTag.tag',
            'discountCode.discountCodeMember' => function ($query) use ($memberID) {
                $query->where('member_id', $memberID);
            }
        ])
            ->whereHas('discountCode', function ($query) {
                $query->where('discount_code_status', 1)
                    ->where('deleted_at', 0);
            })
            ->where('member_id', $memberID)
            ->where('status', 1)
            ->where('used', 1)
            ->where('created_at', '>=', Carbon::today()->subMonths(12)) //  請求日往回推一年，歸戶日要大於期間
            ->get();
    }

    public function setMemberCodeUsedById($memberID,$discountID)
    {

        return $this->model->with([
            'discountCode',
            'discountCode.discountCodeTag.tag',
            'discountCode.discountCodeMember' => function ($query) use ($memberID) {
                    $query->where('member_id', $memberID);
                }
            ])
            ->where('member_id', $memberID)
            ->where('discount_code_id', $discountID)
            ->update([
                'used'  => 1,
            ]);
    }
}
