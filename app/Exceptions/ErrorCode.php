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
        'E0007' => '取得資料錯誤',
        'E0008' => '起始時間不得晚於結束時間',

        'E0011' => '建立會員失敗',
        'E0012' => '註冊失敗',
        'E0013' => '手機驗證碼輸入錯誤或已失效',
        'E0014' => 'Email驗證碼輸入錯誤或已失效',
        'E0015' => '會員已註冊',
        'E0018' => '密碼修改失敗',

        'E0020' => '輸入的帳號密碼有誤，請重試',
        'E0021' => '會員驗證失效',
        'E0022' => '無法證碼Token',
        'E0023' => '無法取得Token',
        'E0025' => 'Token產生失敗',
        'E0026' => 'Token更新失敗',
        'E0027' => '授權方式錯誤',

        'E0040' => '加入收藏失敗',
        'E0041' => '移除收藏失敗',

        'E0051' => 'Email發送失敗',
        'E0052' => '簡訊發送失敗',

        'E0061' => '會員不存在',

        'E0070' => '使用優惠卷失敗',
        'E0071' => '超過可使用優惠卷上限',
        'E0072' => '優惠券已兌換完畢',
        'E0073' => '優惠券已失效',
        'E0074' => 'QR Code已失效',
        'E0075' => 'QR Code已使用',
        'E0076' => '禮物不存在',
        'E0077' => '點數不足',
        'E0078' => '禮物已兌換完畢',
        'E0079' => '已超過可兌換禮物上限',

        'E0080' => '缺少訂單編號',
        'E0081' => '錯誤的訂單編號',

        'E0090' => '找不到該邀請碼對應會員',
        'E0091' => '邀請碼格式錯誤',
        'E0092' => '會員邀請碼不存在',
        'E0093' => '邀請碼無法填寫自己',
        'E0094' => '邀請碼只能填寫一次',


        'E0095' => '通知不存在',




        'E0200' => '加入餐車會員失敗',
        'E0201' => '餐車ID不存在',
        'E0202' => '餐車短網址不存在',

        'E4001' => '您的手機號碼尚未完成驗證，請至會員專區進行手機號碼驗證流程',
        'E4002' => '轉贈對象手機號碼尚未完成驗證，請對方完成手機號碼驗證流程',
        'E4003' => '票卷轉贈失敗',
        'E4004' => '票卷隱藏失敗',
        'E4005' => '查詢高捷憑證失敗',
        'E4006' => '紀錄高捷憑證列印失敗',
        'E4011' => '轉贈票券退回失敗',

        'E0501'  => '輸入錯誤請重新輸入！',
        'E0502'  => '刪除折扣碼失敗',
        'E0503'  => '折價券過期/已用/輸入錯誤/未達滿額折抵/選購之商品不適用，請確認使用規則或重新輸入',

        'E0101' => '訂單不存在',
        'E0102' => '訂單退款失敗',
        'E0103' => '取訂單列表錯誤',

        'E0301' => '手機格式錯誤',

        'E9000' => '取付款資訊失敗',
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
        'E9020' => '無商品資料，加入購物車失敗',

        'E9021' => '取立即購買購物車失敗',

        'E9029' => '商品不存在，無法加入購物車',
        'E9030' => '商品不存在，無法結帳',
        'E9031' => '商品數量，無法結帳',
        'E9032' => '結帳金額錯誤，無法結帳',

        'E9050' => '該訂單不是您所有，無法取得',

        'E9100' => '銷售賣場不存在',

        'E9201' => '購買數量不符，未達優惠條件',
        'E9202' => '購買數量不足，未達優惠條件',
        'E9203' => '購買金額不足，未達優惠條件',
        'E9204' => '銷售時間尚未開始或已結束',

        'E9300' => '申請合作廠商失敗',

        'E9999' => 'ipasspay未知錯誤',

        'A0030' => '請10分鐘後再註冊',
        'A0031' => '該手機號碼已被使用',
        'A0032' => '該Email已被使用',
        'A0033' => '此身分證字號(護照號碼)已存在，請重新填寫',
        'A0034' => '此身分證字號格式錯誤，請重新填寫',
        'A0035' => '至少8個字元，最多16碼，只接受大、小寫英文字母及0-9數字，其他符號如+_)(*&^%$#@!~...等均不接受',
        'A0036' => 'Email格式錯誤',

        'A0101' => '您已經是餐車會員',

        'C0301' => '信用卡付款失敗',
    ];

    public static function message($code)
    {
        $self = new static;
        return (isset($self->errorCodes[$code])) ? $self->errorCodes[$code] : '未知錯誤訊息';
    }
}
