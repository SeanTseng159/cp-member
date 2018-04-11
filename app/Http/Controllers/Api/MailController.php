<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Jobs\Mail\OrderPaymentCompleteMail;

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

        if ($data['memberId'] && $data['source'] && $data['orderId']) {
            dispatch(new OrderPaymentCompleteMail($data['memberId'], $data['source'], $data['orderId']))->delay(5);
        }

        return $this->success();
    }
}
