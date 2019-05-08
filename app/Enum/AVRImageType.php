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


final class AVRImageType extends Enum

{
    const activity = 1;
    const mission = 2;

}