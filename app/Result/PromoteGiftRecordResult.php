<?php
namespace App\Result;

use App\Enum\MyGiftType;
use App\Helpers\CommonHelper;
use Carbon\Carbon;

class PromoteGiftRecordResult
{

    public function list($promoteGifts)
    {
        $result = [];
        foreach ($promoteGifts as $item) {
            $img = optional($item->promoteGift->image);
            $data = new \stdClass();
            $data->id = $item->id;
            $data->name = $item->promoteGift->name;
            $data->title = $item->promoteGift->name;
            $data->duration = Carbon::parse($item->promoteGift->award_validity_end_at)->format('Y-m-d');
            $data->photo = $img->folder ? CommonHelper::getBackendHost($img->folder . $img->filename . '_s.' . $img->ext) : '';
            $data->type = MyGiftType::PROMOTE_GIFT;

            //$status 0:可使用  1:已使用 2:已過期 3:免核銷
            if (is_null($item->verified_at)) {
                if (Carbon::now()->greaterThan($item->promoteGift->usage_end_at)) {
                    $data->status = 2;
                } else {
                    if ($item->promoteGift->verify_status == 1) {
                        $data->status = 3;
                    } else{
                        $data->status = 0;
                    }
                }
            } else {
                $data->status = 1;
            }
            $result[] = $data;
        };

        return $result;
    }

    /** detail
     * @param $promoteGiftRecord
     * @return \stdClass
     */
    public function show($promoteGiftRecord)
    {
        $result = new \stdClass();
        $img = optional($promoteGiftRecord->promoteGift->image);
        $result->id = $promoteGiftRecord->id;
        $result->name = $promoteGiftRecord->promoteGift->name;
        $result->title = $promoteGiftRecord->promoteGift->name;
        $result->duration = Carbon::parse($promoteGiftRecord->promoteGift->award_validity_end_at)->format('Y-m-d');
        $result->photo = $img->folder ? CommonHelper::getBackendHost($img->folder . $img->filename . '_s.' . $img->ext) : '';
        $result->type = MyGiftType::PROMOTE_GIFT;
        $result->content = $promoteGiftRecord->promoteGift->content;
        $result->desc = $promoteGiftRecord->promoteGift->usage_desc;

        if ($promoteGiftRecord->promoteGift->verify_status == 1) {
            $result->status = 3;
        } else {
            $result->status = 0;
        }

        //已使用
        if ($promoteGiftRecord->verified_at) {
            $result->status = 1;
        }

        //已過期
        if (Carbon::now() >= Carbon::parse($promoteGiftRecord->promoteGift->usage_end_at)) {
            $result->status = 2;
        }
        return $result;
    }
}