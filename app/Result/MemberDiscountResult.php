<?php
namespace App\Result;


use App\Helpers\CommonHelper;
use Carbon\Carbon;

class MemberDiscountResult
{
    public function list($memberCoupons)
    {
        $result = [];
        foreach ($memberCoupons as $item) {
            $data = new \stdClass();
            $data->id = $item->id;
            $data->name = $item->discount->name;
            $data->title = $item->discount->desc;
            $data->duration = Carbon::parse($item->discount->end_at)->format('Y-m-d');
            $data->photo =CommonHelper::getBackendHost($item->discount->image_path);


            //$status 0:可使用  1:已使用 2:已過期
            if (Carbon::parse($data->duration) > Carbon::today() ) {
                if(is_null($item->used_time)){
                    $data->status = 0;
                }else{
                    $data->status = 1;
                }
            } else
                $data->status = 2;
            $data->type = 'discount';
            $result[] = $data;
        }
        return $result;
    }

}