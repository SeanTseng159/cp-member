<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Config\Ticket;

use App\Config\BaseConfig;

class TicketConfig extends BaseConfig
{
    # 票券狀態 (in DB)
    const DB_STATUS = [
        '0' => 10,
        '1' => 11,
        '2' => 11,
        '3' => 10,
        '4' => 11
    ];
}
