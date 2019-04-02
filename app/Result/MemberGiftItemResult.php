<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/6
 * Time: 下午 05:33
 */

namespace App\Result;


use App\Enum\ClientType;
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
    public function list($memberGifts, $type)
    {
        $result = [];
        foreach ($memberGifts as $item)
        {
            $data = $this->arrangeData($item, $type);
            if ($data)
            {
                $result[] = $data;
            }
            
        }
        
        return $result;
    }
    
    public function show($memberGift)
    {
        return $this->arrangeData($memberGift);
    }
    
    /**
     * @param       $type
     * @param       $item
     *
     * @return \stdClass
     */
    public function arrangeData($item, $type = null)
    {
        if (!$item)
        {
            return null;
        }
        
        $data = new \stdClass();
        
        $gift = $item->gift;
        $diningCar = $gift->diningCar;
        
        $data->id = $item->id;
        $data->Name = $diningCar->name;
        $data->title = $gift->name;
        $data->duration = Carbon::parse($gift->expire_at)->format('Y-m-d');
        $data->photo = ImageHelper::getImageUrl(ClientType::gift, $gift->id);


        $data->status = 0;
        
        //detail's information
        if (!$type)
        {
            $data->content = $gift->content;
            $data->desc = $gift->desc;
        }
        
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
        
        if (!$type)
        {
            return $data;
        }
        //可使用
        else if ($type == 1 && $data->status == 0)
        {
            return $data;
        }
        //已使用或過期
        else if ($type == 2 && $data->status != 0)
        {
            return $data;
        }
        
        
    }
    
}