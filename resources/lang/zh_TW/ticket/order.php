<?php
return [
    'status' => [
        'waiting' => '待付款',
        'repay' => '重新付款',
        'pay_failure' => '付款失敗',
        'pay_expired' => '已失效',
        'paid' => '已付款',
        'applied' => '已申退',
        'processing' => '處理中',
        'processed' => '已處理',
        'partial_refund' => '部份退貨',
        'refunded' => '已退貨',
        'pay_complete' => '已完成',
        'pay_cancal' => '已取消'
    ],
    'usedStatus' => [
        'reserved' => '保留中',
        'unused' => '未使用',
        'used' => '已使用',
        'refunding' => '退貨中',
        'refunded' => '已退貨',
        'transfer' => '已轉贈',
        'expired' => '已失效'
    ],
    'payment' => [
    	'gateway' => [
    		'unknown' => '未知',
    		'ipasspay' => 'iPassPay',
    		'tspg' => '台新',
            'linepay' => 'LINE Pay',
            'neweb' => '藍新'
    	],
    	'method' => [
    		'unknown' => '未知',
    		'credit_card' => '信用卡一次付清',
    		'atm' => 'ATM虛擬帳號',
    		'ipasspay' => 'iPassPay',
            'linepay' => 'LINE Pay',
            'googlepay' => 'Google Pay',
            'applepay' => 'Apple Pay',
            'taiwanpay' => '台灣Pay'
    	],
    	'ipasspay' => [
    		'ACCLINK' => '約定連結帳戶付款',
    		'CREDIT' => '信用卡',
    		'VACC' => '銀行ATM',
    		'BARCODE' => '超商條碼',
    		'ECAC' => '電子支付帳戶'
    	]
    ]
];
