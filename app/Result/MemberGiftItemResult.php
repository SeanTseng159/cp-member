<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/6
 * Time: 下午 05:33
 */

namespace App\Result;


use App\Helpers\ClientType;
use App\Helpers\ImageHelper;
use Carbon\Carbon;

class MemberGiftItemResult
{
    /**
     * 取得禮物列表
     *
     * @param $memberGifts
     *
     * @param $type
     *
     * @return array
     */
    public function list($memberGifts,$type)
    {
        $result = [];
        foreach ($memberGifts as $item)
        {
            $data = new \stdClass();
            $gift = $item->gift;
            $diningCar = $gift->diningCar;
        
            $data->id = $item->id;
            $data->Name = $diningCar->name;
            $data->title = $gift->name;
            $data->duration = $gift->expire_at;
            $data->photo = ImageHelper::getImageUrl(ClientType::gift,$gift->id,1);
            $data->status = 0;
        
            //已使用
            if ($item->used_time)
            {
                $data->status = 1;
            }
        
            //已過期
            if (Carbon::now() >= Carbon::parse($gift->expire_at))
            {
                $data->status = 2;
            }
        
            if ($type == 1 && $data->status == 0)
            {
                $result[] = $data;
            }
            else if ($type == 2 && $data->status != 0)
            {
                $result[] = $data;
            }
        }
    
        return $result;
    }
    
}