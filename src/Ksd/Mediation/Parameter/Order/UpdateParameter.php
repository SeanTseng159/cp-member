<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/13
 * Time: 下午 2:01
 */

namespace Ksd\Mediation\Parameter\Order;


use Ksd\Mediation\Parameter\BaseParameter;

class UpdateParameter extends BaseParameter
{
    public function laravelRequest($request)
    {
        $this->source = $request->input('source');
        $this->id = $request->input('id');
        $this->paySource = $request->input('paySource');
//        $this->payResult = $request->input('payResult');

        //ipasspay回傳資料
        $this->orderNo = $request->input('orderNo');
        $this->order_id = $request->input('order_id');
        $this->status = $request->input('status');
        $this->txnseq = $request->input('txnseq');
        $this->payment_type = $request->input('payment_type');
        $this->amount = $request->input('amount');
        $this->discount_amt = $request->input('discount_amt');
        $this->redeem_amt = $request->input('redeem_amt');
        $this->pay_amt = $request->input('pay_amt');
        $this->pay_time = $request->input('pay_time');
        $this->fund_time = $request->input('fund_time');
        $this->respond_code = $request->input('respond_code');
        $this->auth = $request->input('auth');
        $this->card6no = $request->input('card6no');
        $this->card4no = $request->input('card4no');
        $this->eci = $request->input('eci');
        $this->signature = $request->input('signature');


    }

}