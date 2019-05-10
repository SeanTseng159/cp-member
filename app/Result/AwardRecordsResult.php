<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/6
 * Time: 下午 05:33
 */

namespace App\Result;


use App\Enum\MyGiftType;
use Carbon\Carbon;

class AwardRecordsResult
{

    public function list($awardList)
    {
        $result = [];
        foreach ($awardList as $item) {
            $data = new \stdClass();
            $data->id = $item->award_id;
            $data->Name = $item->award->supplier->supplier_name;
            $data->title = $item->award->award_name;
            $data->duration = Carbon::parse($item->award->award_validity_end_at)->format('Y-m-d');
            $data->photo = $item->award->image->img_path;

            //$status 0:可使用  1:已使用 2:已過期
            if (is_null($item->verified_at)) {
                if (Carbon::now()->greaterThan($item->award->award_validity_end_at)) {
                    $data->status = 2;
                } else {
                    $data->status = 0;
                }
            } else
                $data->status = 1;
            $data->type = MyGiftType::award;
            $result[] = $data;
        }
        return $result;
    }


}