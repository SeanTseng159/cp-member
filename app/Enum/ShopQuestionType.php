<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/11
 * Time: 下午 12:05
 */


namespace App\Enum;

use BenSampo\Enum\Enum;

final class ShopQuestionType extends Enum
{
    //題目類型:0:星星 1:問答 2:單選 3:多選
    const Star = 0;
    const QA = 1;
    const Single = 2;
    const Multiple = 3;
}