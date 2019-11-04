<?php
/**
 * Created by PhpStorm.
 * User: Annie
 * Date: 2019/3/11
 * Time: 下午 12:05
 */


namespace App\Enum;

use BenSampo\Enum\Enum;

final class WaitingStatus extends Enum
{
    //等候狀態 0:等待交號 1:目前叫號 2:已叫號
    const Waiting = 0;
    const  Called = 1;
}