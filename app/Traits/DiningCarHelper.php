<?php
/**
 * User: lee
 * Date: 2019/02/19
 * Time: 上午 10:03
 */

namespace App\Traits;

trait DiningCarHelper
{
    /**
     * 取會員等級
     * @param App\Models\Ticket\DiningCarMemberLevel
     * @param $amount
     */
    private function getMemberLevel($memberLevels, $amount = 0)
    {
        if ($memberLevels->isEmpty()) return 0;

        $memberLevel = $memberLevels->last(function ($item) use ($amount) {
            return $item->limit <= $amount;
        });

        return ($memberLevel) ? $memberLevel->level : 0;
    }
}
