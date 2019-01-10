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
        '4' => null
    ];

    # 票券狀態 (in DB)
    const DB_STATUS_NAME = [
        '0' => '未使用',
        '1' => '已使用',
        '2' => '使用中',
        '3' => '已失效',
        '4' => '已轉贈',
        '99' => '無'
    ];
}
