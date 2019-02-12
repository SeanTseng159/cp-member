<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Config\Ticket;

use App\Config\BaseConfig;

class DiningCarConfig extends BaseConfig
{
    # 開店狀態
    const OPEN_STATUS = [
        0 => '休息中',
        1 => '準備中',
        2 => '營業中'
    ];
}
