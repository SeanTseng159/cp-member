<?php
/**
 * User: lee
 * Date: 2019/03/05
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use Carbon\Carbon;

class GiftResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取詳細資料
     * @param $gift
     * @return \stdClass|null
     */
    public function detailByJoinDiningCar($gift)
    {
        if (!$gift) return null;

        $result = new \stdClass;
        $result->id = $gift->id;
        $result->name = $gift->name;

        return $result;
    }


    public function list($gifts)
    {
        if (!$gifts) return null;

        $result = [];
        foreach ($gifts as $item) {
            $data = new \stdClass();
            $data->id = $item->id;
            $data->name = $item->name;
            $data->points = $item->points;
            $data->status = $item->status;
            $data->photo = $item->photo;
            $data->duration = Carbon::parse($item->expire_at)->format('Y-m-d');
            $data->desc = $item->desc;
            $data->content = $item->content;
            $result[] = $data;
        }

        return $result;

    }
}
