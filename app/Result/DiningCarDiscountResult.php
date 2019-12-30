<?php
/**
 * Created by Fish.
 * 2019/12/20 2:13 下午
 */

namespace App\Result;

use App\Helpers\CommonHelper;
use App\Helpers\ImageHelper;
use Carbon\Carbon;

class DiningCarDiscountResult
{

    public function show($diningCarDiscount)
    {
        $result = new \stdClass();
        $result->name = '多店可用';
        $result->photo = CommonHelper::getBackendHost($diningCarDiscount->image_path);
        $result->title = $diningCarDiscount->name;
        $result->duration = $this->getExpirationDateBy($diningCarDiscount,'start_at', 'end_at');
        $result->content = $diningCarDiscount->desc;
        $result->desc = $diningCarDiscount->desc;
//        $result->status = $diningCarDiscount->
        return $result;
    }

    private function getExpirationDateBy($model, $startAtColumnName, $endAtColumnName)
    {
        $startAt = Carbon::parse($model->$startAtColumnName);
        $endAt = Carbon::parse($model->$endAtColumnName);
        return $startAt->format('Y/m/d H:i')." ~ ".$endAt->format('Y/m/d H:i');
    }
}