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

class AwardRecordResult
{

    public function list($awardList)
    {
        $result = [];
        foreach ($awardList as $item) {
            $data = new \stdClass();
            $data->id = $item->award_record_id;
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

    /** detail
     * @param $awardRecord
     * @return \stdClass
     */
    public function show($awardRecord)
    {
        $result = new \stdClass();
        $result->name = $awardRecord->award->supplier->supplier_name;


        $result->phote = $awardRecord->award->image->img_path;
        $result->title = $awardRecord->award->award_name;
        $result->duration = Carbon::parse($awardRecord->award->award_validity_end_at)->format('Y-m-d');
        $result->content = $awardRecord->award->award_name;
        $result->desc = $awardRecord->award->award_description;
        $result->status = 0;

        //已使用
        if ($awardRecord->verified_at) {
            $result->status = 1;
        }
        //已過期
        if (Carbon::now() >= Carbon::parse($awardRecord->award->award_validity_end_at)) {
            $result->status = 2;
        }
        return $result;

    }


}