<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Config\Ticket;

use App\Config\BaseConfig;

class OrderConfig extends BaseConfig
{
    # 訂單狀態
    /*const STATUS_WAITING = 'waiting';
    const STATUS_REPAY = 'repay';
    const STATUS_PAY_FAILURE = 'pay_failure';
    const STATUS_PAY_EXPIRED = 'pay_expired';
    const STATUS_PAID = 'paid';
    const STATUS_APPLIED = 'applied';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';
    const STATUS_PARTIAL_REFUND = 'partial_refund';
    const STATUS_REFUNDED = 'refunded';*/

    const ORG_STATUS_CODE_WAITING = '00';
    const ORG_STATUS_CODE_REPAY = '03';
    const ORG_STATUS_CODE_PAY_FAILURE = '01';
    const ORG_STATUS_CODE_PAY_EXPIRED = '02';
    const ORG_STATUS_CODE_PAID = '10';
    const ORG_STATUS_CODE_APPLIED = '20';
    const ORG_STATUS_CODE_PROCESSING = '21';
    const ORG_STATUS_CODE_PROCESSED = '22';
    const ORG_STATUS_CODE_PARTIAL_REFUND = '23';
    const ORG_STATUS_CODE_REFUNDED = '24';

    const ORG_STATUS = [
        SELF::ORG_STATUS_CODE_WAITING => 'waiting',
        SELF::ORG_STATUS_CODE_REPAY => 'repay',
        SELF::ORG_STATUS_CODE_PAY_FAILURE => 'pay_failure',
        SELF::ORG_STATUS_CODE_PAY_EXPIRED => 'pay_expired',
        SELF::ORG_STATUS_CODE_PAID => 'paid',
        SELF::ORG_STATUS_CODE_APPLIED => 'applied',
        SELF::ORG_STATUS_CODE_PROCESSING => 'processing',
        SELF::ORG_STATUS_CODE_PROCESSED => 'processed',
        SELF::ORG_STATUS_CODE_PARTIAL_REFUND => 'partial_refund',
        SELF::ORG_STATUS_CODE_REFUNDED => 'refunded'
    ];

    const STATUS_CODE_WAITING = '00';
    const STATUS_CODE_COMPLETE = '01';
    const STATUS_CODE_PARTIAL_REFUND = '02';
    const STATUS_CODE_REFUNDED = '03';
    const STATUS_CODE_PROCESSING = '04';
    const STATUS_CODE_REPAY = '07';
    const STATUS_CODE_PAY_CANCAL = '08';

    const STATUS = [
        SELF::STATUS_CODE_WAITING => 'waiting',
        SELF::STATUS_CODE_COMPLETE => 'pay_complete',
        SELF::STATUS_CODE_PARTIAL_REFUND => 'partial_refund',
        SELF::STATUS_CODE_REFUNDED => 'refunded',
        SELF::STATUS_CODE_PROCESSING => 'processing',
        SELF::STATUS_CODE_REPAY => 'repay',
        SELF::STATUS_CODE_PAY_CANCAL => 'pay_cancal'
    ];

    const USED_STATUS_CODE_NOT_PAY = '00';
    const USED_STATUS_CODE_EXPIRED = '01';
    const USED_STATUS_CODE_TRANSFER = '05';
    const USED_STATUS_CODE_NOT_USE = '10';
    const USED_STATUS_CODE_UESD = '11';
    const USED_STATUS_CODE_APPLIED = '20';
    const USED_STATUS_CODE_PROCESSING = '21';
    const USED_STATUS_CODE_PROCESSED = '22';
    const USED_STATUS_CODE_REFUNDED = '23';
    const USED_STATUS_CODE_UNKNOWN = '99';

    const USED_STATUS = [
        SELF::USED_STATUS_CODE_NOT_PAY => 'reserved',
        SELF::USED_STATUS_CODE_EXPIRED => 'expired',
        SELF::USED_STATUS_CODE_TRANSFER => 'transfer',
        SELF::USED_STATUS_CODE_NOT_USE => 'unused',
        SELF::USED_STATUS_CODE_UESD => 'used',
        SELF::USED_STATUS_CODE_APPLIED => 'refunding',
        SELF::USED_STATUS_CODE_PROCESSING => 'refunding',
        SELF::USED_STATUS_CODE_PROCESSED => 'refunding',
        SELF::USED_STATUS_CODE_REFUNDED => 'refunded',
        SELF::USED_STATUS_CODE_UNKNOWN => 'unknown'
    ];

    # 訂單金流閘道
    const PAYMENT_GATEWAY = [
        0 => 'unknown',
        1 => 'neweb',
        2 => 'ipasspay',
        3 => 'tspg',
        4 => 'linepay',
        5 => 'taiwanpay'
    ];

    # 訂單金流方式
    const PAYMENT_METHOD = [
        0 => 'unknown',
        111 => 'credit_card',
        211 => 'atm',
        411 => 'ipasspay',
        611 => 'linepay',
        711 => 'googlepay',
        811 => 'applepay',
        911 => 'taiwanpay'
    ];

    # 銀行名稱
    const BANK_NAME = [
        '700' => 'post',
        '812' => 'tspg'
    ];

    # 設備來源
    const PAYMENT_DEVICE = [
        'unknown' => 0,
        'web' => 10,
        'mobile' => 11,
        'ios' => 12,
        'android' => 13
    ];

    # 物流狀態
    const SHIPMENT_STATUS = [
        1 => '備貨中',
        2 => '出貨中',
        3 => '已出貨',
        4 => '已到達',
        5 => '已取貨',
        6 => '已退貨'
    ];
}
