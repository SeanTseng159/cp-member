<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result\Ticket;

use App\Enum\DiningCarPointRecordType;
use App\Result\BaseResult;
use Carbon\Carbon;


class DiningCarPointResult extends BaseResult
{


    public function __construct()
    {
        parent::__construct();
    }


    public function list($result)
    {
        $resultAry = [];

        foreach ($result as $item) {
            $data = new \stdClass();
            $data->time = Carbon::parse($item->created_at)->format('Y-m-d h:m');
            $data->content = new \stdClass();
            if ($item->model_type == DiningCarPointRecordType::getKey(DiningCarPointRecordType::gift)) {

                $gift = $item->gifts;
                $qty = ($item->point * -1) / $gift->points;
                $data->content->desc = "使用%s點兌換了{$qty}份「{$gift->name}」";
                $data->content->point = $item->point * -1;
            } else if ($item->model_type == DiningCarPointRecordType::getKey(DiningCarPointRecordType::dining_car_point_rule)) {

                $rule = $item->pointRules;

                if($rule->type == 1)//按比例
                {
                    $orignal = ($item->point)*($rule->point);
                    $data->content->desc = "消費{$orignal}元，累積了點數%s點";
                    $data->content->point = $item->point;
                }
                elseif ($rule->type==2) //直接發
                {
                    $data->content->desc = "因為活動額外獲得%s點";
                    $data->content->point = $rule->point;

                }

            }
            $resultAry[] = $data;


        }
        return $resultAry;
    }


}
