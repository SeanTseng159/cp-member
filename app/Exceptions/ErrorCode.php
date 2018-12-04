<?php
/**
 * User: lee
 * Date: 2018/03/07
 * Time: 上午 9:30
 */

namespace App\Exceptions;

class ErrorCode
{
    /**
     * A list of the exception types.
     *
     * @var array
     */
    protected $errorCodes = [
        'E0001' => '傳送參數錯誤',
        'E0002' => '新增失敗',
        'E0003' => '更新失敗',
        'E0004' => '刪除失敗',
        'E0005' => '連線品質不佳，請重整或刷新頁面',
        'E0006' => '缺少必要參數',

        'E0011' => '建立會員失敗',
        'E0012' => '註冊失敗',
        'E0013' => '手機驗證碼輸入錯誤或已失效',
        'E0014' => 'Email驗證碼輸入錯誤或已失效',
        'E0018' => '密碼修改失敗',

        'E0020' => '輸入的帳號密碼有誤，請重試',
        'E0021' => '會員驗證失效',
        'E0022' => '無法證碼Token',
        'E0023' => '無法取得Token',
        'E0025' => 'Token產生失敗',
        'E0026' => 'Token更新失敗',
        'E0027' => '授權方式錯誤',

        'E0051' => 'Email發送失敗',
        'E0052' => '簡訊發送失敗',

        'E0061' => '會員不存在',

        'E4001' => '您的手機號碼尚未完成驗證，請至會員專區進行手機號碼驗證流程',
        'E4002' => '轉贈對象手機號碼尚未完成驗證，請對方完成手機號碼驗證流程',
        'E4003' => '票卷轉贈失敗',
        'E4004' => '票卷隱藏失敗',
        'E4011' => '轉贈票券退回失敗',

        'E0101' => '訂單不存在',
        'E0102' => '訂單退款失敗',

        'E0301' => '手機格式錯誤',

        'E9001' => '結帳(取單號)失敗',
        'E9002' => '設定物流失敗',
        'E9003' => '刷卡失敗，請重新嘗試',
        'E9006' => '結帳失敗，請重新嘗試',
        'E9007' => '該訂單已無法付款，請重新選購',
        'E9008' => '取虛擬帳號失敗，請重新嘗試',
        'E9009' => '部分商品已下架，請重新結帳',
        'E9010' => '商品無法銷售，請重新選購',
        'E9011' => '商品庫存不足，請重新選購',
        'E9012' => '商品超過可購買數量，請重新選購',
        'E9013' => '加入購物車失敗，請重新嘗試',
        'E9014' => 'LinePay付款失敗，請重新嘗試',
        'E9015' => '信用卡付款失敗，請重新嘗試',
        'E9016' => '訂單不存在，無法重新付款',
        'E9017' => '該訂單不是您所有，無法重新付款',
        'E9018' => '收件人手機格式錯誤',
        'E9019' => '購物車來源是不是允許',

        'E9021' => '取立即購買購物車失敗',

        'E9030' => '商品不存在，無法結帳',
        'E9031' => '商品數量，無法結帳',
        'E9032' => '結帳金額錯誤，無法結帳',

        'E9050' => '該訂單不是您所有，無法取得',

        'E9999' => 'ipasspay未知錯誤',

        'A0030' => '請10分鐘後再註冊',
        'A0031' => '該手機號碼已被使用',
        'A0032' => '該Email已被使用',
        'A0033' => '此身分證字號(護照號碼)已存在，請重新填寫',
        'A0034' => '此身分證字號格式錯誤，請重新填寫',
        'A0035' => '至少8個字元，最多16碼，只接受大、小寫英文字母及0-9數字，其他符號如+_)(*&^%$#@!~...等均不接受',

        'C0301' => '信用卡付款失敗',
    ];

    public static function message($code)
    {
        $self = new static;
        return (isset($self->errorCodes[$code])) ? $self->errorCodes[$code] : '未知錯誤訊息';
    }
}
