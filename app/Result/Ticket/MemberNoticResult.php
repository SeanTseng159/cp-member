<?php
/**
 * User: Danny
 * Date: 2019/07/18
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;

class MemberNoticResult extends BaseResult
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取通知列表
     * @param $data
     */
    public function list($infos)
    {
        if ($infos->isEmpty()) return [];

        $noticInfos = [];
        foreach ($infos as $info) {
            $noticInfo[] = $this->getNotic($info);
        }

        return $noticInfo;
    }

    /**
     * 通知消息資訊
     * @param $newsfeed
     */
    public function getNotic($info)
    {
        if (!$info) return null;
        $url = env('BACKEND_DOMAIN');

        $result = new \stdClass;
        $result->id = $info->id;
        $result->diningCarName = $info->diningCar->name;
        $result->imgUrl = $url.'/'.$info->mainImg->folder.$info->mainImg->filename.'.'.$info->mainImg->ext;
        $result->message = $info->notification_message;
        $result->prodType = $info->prod_type;
        $result->prodId = $info->prod_id;
        $result->sendTime =date("Y-m-d",strtotime($info->created_at->toDateTimeString()));
        $result->readStatus = $info->read_status;

        return $result;
    }
}
