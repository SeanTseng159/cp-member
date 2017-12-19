<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/30
 * Time: 上午 11:53
 */

namespace Ksd\Mediation\Parameter\Checkout;

use Ksd\Mediation\Parameter\BaseParameter;

class PostBackParameter extends BaseParameter
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
        $this->tx_type = $request->input('tx_type');
        $this->order_no = $request->input('order_no');
        $this->ret_msg = $request->input('ret_msg');
        $this->auth_id_resp = $request->input('auth_id_resp');

    }

}