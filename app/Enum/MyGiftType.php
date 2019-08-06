<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/11
 * Time: 下午 12:05
 */


namespace App\Enum;

use BenSampo\Enum\Enum;


final class MyGiftType extends Enum
{
    const gift = 'gift';
    const award = 'award';
    const PROMOTE_GIFT = 'promoteGift';
}