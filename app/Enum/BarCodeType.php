<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/5
 * Time: 下午 12:05
 */


namespace App\Enum;

use BenSampo\Enum\Enum;


final class BarCodeType extends Enum

{
    const code_39 = 1;
    const code_128 = 2;
}