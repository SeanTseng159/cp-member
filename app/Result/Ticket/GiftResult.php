<?php
/**
 * User: lee
 * Date: 2019/03/05
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;

class GiftResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取詳細資料
     * @param $data
     */
    public function detailByJoinDiningCar($gift)
    {
        if (!$gift) return null;

        $result = new \stdClass;
        $result->id = $gift->id;
        $result->name = $gift->name;

        return $result;
    }
}
