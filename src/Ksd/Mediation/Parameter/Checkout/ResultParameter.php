<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/30
 * Time: 上午 11:53
 */

namespace Ksd\Mediation\Parameter\Checkout;

use Ksd\Mediation\Parameter\BaseParameter;

class ResultParameter extends BaseParameter
{
    /**
     * 處理 ci request
     * @param $input
     */
    public function codeigniterRequest($no, $input = null)
    {
        parent::codeigniterRequest($input);
        $this->no = $no;
        $this->source = $input->get('source');
    }


    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($request = null)
    {
        parent::laravelRequest($request);
        $this->ret_code = $request->input('ret_code');
        $this->ret_msg = $request->input('ret_msg');
        $this->order_no = $request->input('order_no');
        $this->auth_id_resp = $request->input('auth_id_resp');
        $this->rrn = $request->input('rrn');
        $this->order_status = $request->input('order_status');
        $this->auth_type = $request->input('auth_type');
        $this->cur = $request->input('cur');
        $this->purchase_date = $request->input('purchase_date');
        $this->tx_amt = $request->input('tx_amt');
        $this->settle_amt = $request->input('settle_amt');
        $this->settle_seq = $request->input('settle_seq');
        $this->settle_date = $request->input('settle_date');
        $this->refund_trans_amt = $request->input('refund_trans_amt');
        $this->refund_rrn = $request->input('refund_rrn');
        $this->refund_auth_id_resp = $request->input('refund_auth_id_resp');
        $this->refund_date = $request->input('refund_date');

    }

}