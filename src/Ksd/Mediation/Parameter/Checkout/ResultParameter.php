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
        $body = $request->getContent();
        if (!empty($body)) {
            $parameters = json_decode($body, true);
            $this->ret_code = array_get($parameters, 'params.ret_code');
            $this->ret_msg = array_get($parameters, 'params.ret_msg');
            $this->order_no = array_get($parameters, 'params.order_no');
            $this->auth_id_resp = array_get($parameters, 'params.auth_id_resp');
            $this->rrn = array_get($parameters, 'params.rrn');
            $this->order_status = array_get($parameters, 'params.order_status');
            $this->auth_type = array_get($parameters, 'params.auth_type');
            $this->cur = array_get($parameters, 'params.cur');
            $this->purchase_date = array_get($parameters, 'params.purchase_date');
            $this->tx_amt = array_get($parameters, 'params.tx_amt');
            $this->settle_amt = array_get($parameters, 'params.settle_amt');
            $this->settle_seq = array_get($parameters, 'params.settle_seq');
            $this->settle_date = array_get($parameters, 'params.settle_date');
            $this->refund_trans_amt = array_get($parameters, 'params.refund_trans_amt');
            $this->refund_rrn = array_get($parameters, 'params.refund_rrn');
            $this->refund_auth_id_resp = array_get($parameters, 'params.refund_auth_id_resp');
            $this->refund_date = array_get($parameters, 'params.refund_date');
        }
    }

}