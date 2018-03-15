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
        'E0005' => '資料無法取得',

        'E0011' => '建立會員失敗',
        'E0012' => '註冊失敗',
        'E0013' => '電話驗證碼錯誤',
        'E0014' => 'Email驗證碼錯誤',
        'E0018' => '密碼修改失敗',

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
        'E9003' => '刷卡失敗',
        'E9009' => '結帳失敗，部分商品已下架',

        'E9999' => 'ipasspay未知錯誤',

        'A0030' => '請10分鐘後再註冊',
        'A0031' => '該手機號碼已被使用',
        'A0032' => '該Email已被使用',
        'A0033' => '此身分證字號(護照號碼)已存在，請重新填寫',
        'A0034' => '此身分證字號格式錯誤，請重新填寫',

        'C0301' => '信用卡付款失敗',
    ];

    public static function message($code)
    {
        $self = new static;
        return (isset($self->errorCodes[$code])) ? $self->errorCodes[$code] : '未知錯誤訊息';
    }
}
