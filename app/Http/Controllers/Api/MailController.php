<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Jobs\Mail\OrderPaymentCompleteMail;
use App\Jobs\SMS\OrderPaymentComplete as OrderPaymentCompleteSMS;

use App\Jobs\Mail\MagentoOrderATMCompleteMail;

class MailController extends RestLaravelController
{

    public function __construct()
    {

    }

    /**
     * 發送繳款完成通知信
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentComplete(Request $request)
    {
        $data = $request->only([
            'memberId',
            'source',
            'orderId'
        ]);

        if ($data['memberId'] == 0) {
            // 訪客
            dispatch(new OrderPaymentCompleteSMS($data['orderId']))->delay(10);
        }
        elseif ($data['memberId'] && $data['source'] && $data['orderId']) {
            // 一般會員
            dispatch(new OrderPaymentCompleteMail($data['memberId'], $data['source'], $data['orderId']))->delay(10);
        }

        return $this->success();
    }
}
