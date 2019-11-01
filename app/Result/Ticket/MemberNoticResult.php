<?php
/**
 * User: Danny
 * Date: 2019/07/18
 * Time: 上午 10:03
 */

namespace App\Result\Ticket;

use App\Result\BaseResult;
use App\Helpers\CommonHelper;

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
        $url = optional($info->mainImg)->folder.optional($info->mainImg)->filename.'.'.optional($info->mainImg)->ext;
        $diningCarUrl = CommonHelper::getBackendHost($url);
        $result = new \stdClass;
        $result->id = $info->id;
        $result->diningCarName = empty($info->diningCar) ? 'CityPass都會通' : $info->diningCar->name;
        $result->imgUrl = $diningCarUrl == CommonHelper::getBackendHost().'.' ? 'https://scontent.fkhh1-1.fna.fbcdn.net/v/t1.0-9/60443313_2371437916474915_6204651090290409472_n.jpg?_nc_cat=102&_nc_oc=AQn8HkrJaV57l3fCG1y3rpFKiWu_Lq8Jg8df2bmx_iJV4itYAWOhPDOiQkgAmi-o3QE&_nc_ht=scontent.fkhh1-1.fna&oh=fbac926b0cba076eee39010173c3784f&oe=5DC24FDB' : $diningCarUrl;
        $result->message = $info->notification_message;
        $result->prodType = $info->prod_type;
        $result->prodId = $info->prod_id;
        $result->url = $info->url;
        $result->sendTime =date("Y-m-d",strtotime($info->created_at->toDateTimeString()));
        $result->readStatus = $info->read_status;

        return $result;
    }
}
