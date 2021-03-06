<?php
/**
 * Created by Fish.
 * 2019/12/23 4:44 下午
 */

namespace App\Result\Ticket;

use App\Helpers\CommonHelper;
use Carbon\Carbon;

class MemberDiningCarDiscountResult
{
    public function show($memberDiningCarDiscount)
    {
        // dd($memberDiningCarDiscount);
        $result = new \stdClass();

        $result->name = '多店可用';
        $result->photo = CommonHelper::getBackendHost($memberDiningCarDiscount->discount->image_path);
        $result->title = $memberDiningCarDiscount->discount->name;
        $result->duration = $this->getExpirationDateBy(
            $memberDiningCarDiscount->discount,
            'start_at',
            'end_at'
        );
        $result->content = $memberDiningCarDiscount->discount->desc;
        $result->desc = $memberDiningCarDiscount->discount->desc;
        $result->status = $this->checkUsageStatusBy(
            $memberDiningCarDiscount->discount->end_at,
            $memberDiningCarDiscount->used_time
        );

        return $result;
    }


    private function getExpirationDateBy($model, $startAtColumnName, $endAtColumnName)
    {
        $startAt = Carbon::parse($model->$startAtColumnName);
        $endAt = Carbon::parse($model->$endAtColumnName);
        return $startAt->format('Y/m/d H:i')." ~ ".$endAt->format('Y/m/d H:i');
    }

    private function checkUsageStatusBy($endAt, $usedTime)
    {
        //$status 0:可使用  1:已使用 2:已過期 3:免核銷

        if (!$usedTime)
        {
            $now = Carbon::now();
            $endAt = Carbon::parse($endAt);

            if ($now->lessThanOrEqualTo($endAt))
            {
                return 0;
            }
            else
            {
                return 2;
            }
        }
        else
        {
            return 1;
        }
    }
}