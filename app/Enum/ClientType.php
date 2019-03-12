<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/5
 * Time: 下午 12:05
 */


namespace App\Enum;

use App\Models\Gift;
use App\Models\Ticket\DiningCar;
use BenSampo\Enum\Enum;


final class ClientType extends Enum
{
    const dining_car = 1;
    const coupon = 2;
    const gift = 3;

    private static $type = 0;


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
        // todo chain
        $return = '';

        switch ($typeName) {
            case 'DiningCar':
                $return = self::dining_car;
                break;

            default:
                break;
        }
        return $return;

    }

    public static function getClass($enumType)
    {
        $className = '';
        switch ($enumType) {
            case self::dining_car:
                $className = DiningCar::class;
                break;
            case self::gift:
                $className = Gift::class;
                break;
        }
        return $className;

    }

}