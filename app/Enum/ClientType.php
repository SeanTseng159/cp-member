<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/5
 * Time: 下午 12:05
 */


namespace App\Helpers;


use BenSampo\Enum\Enum;
use function GuzzleHttp\default_user_agent;

final class ClientType extends Enum
{
    const dining_car = 1;
    const coupon = 2;
    const gift = 3;
    
    
    /**
     * 轉換從url來的字串 ex  v1/gift/list?type=current&client=DiningCar&uid=1
     * client=DiningCar -> 1
     *
     * @param $typeName
     *
     * @return int
     */
    public static function transform($typeName)
    {
        switch ($typeName)
        {
            case 'DiningCar':
                return self::dining_car;
                break;
            
            default:
                return 0;
        }
        
    }
    
}