<?php
namespace App\Result;

use App\Enum\MyGiftType;
use App\Helpers\CommonHelper;
use Carbon\Carbon;

class MemberCouponRecordResult
{
    public function list($memberCoupons)
    {
        $result = [];
        foreach ($memberCoupons as $item) {
            $data = new \stdClass();
            $data->id = $item->id;
            $data->name = $item->Name;
            $data->title = $item->title;
            $data->duration = Carbon::parse(explode("~", $item->duration)[1])->format('Y-m-d');
            $data->photo ='';


            //$status 0:可使用  1:已使用 2:已過期
            if (Carbon::parse($data->duration) > Carbon::today() ) {
                if($item->count >= $item->limit_qty){
                    $data->status = 1;
                }else{
                    $data->status = 0;
                }
            } else
                $data->status = 2;
            $data->type = 'coupon';
            $result[] = $data;
        }
        return $result;
    }

}