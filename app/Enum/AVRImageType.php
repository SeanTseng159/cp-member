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
    const avr_activity = 'avr_activity';
    const avr_mission = 'mission';
    const landmark = 'landmark';
    const landmark_category = 'landmark_category';
}