<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Config\Ticket;

use App\Config\BaseConfig;

class ProcuctConfig extends BaseConfig
{
    # 商品類型
    const TYPE_GIFT          = 0;    // 附贈商品
    const TYPE_SINGLE        = 1;    // 單一商品
    const TYPE_COMBO         = 2;    // 組合商品(主商品)
    const TYPE_ADDITIONAL    = 3;    // 加購商品
    const TYPE_COMBO_CONTENT = 4;    // 組合商品(子商品)

    # 商品銷售狀態
    const SALE_STATUS_NOT_YET = 'not_yet';
    const SALE_STATUS_ON_SALE = 'on_sale';
    const SALE_STATUS_OFF_SALE = 'off_sale';
    const SALE_STATUS_STOP_SALE = 'stop_sale';

    const SALE_STATUS = [
        SELF::SALE_STATUS_NOT_YET => '00',
        SELF::SALE_STATUS_ON_SALE => '11',
        SELF::SALE_STATUS_OFF_SALE => '10',
        SELF::SALE_STATUS_STOP_SALE => '20'
    ];
}
