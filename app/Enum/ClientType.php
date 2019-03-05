<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/5
 * Time: 下午 12:05
 */


namespace App\Helpers;

//http://www.php.net/manual/en/class.splenum.php
use SplEnum;



class ClientType extends SplEnum {
    
    const __default = self::dining_car;
    
    const dining_car = 1;
    
    
    static public function transform($value)
    {
        $retValue = 0 ;
        switch ($value)
        {
            case 'DiningCar':
                $retValue = self::dining_car;
                break;
        }
        return $retValue;
    }
    
    
    
    

}