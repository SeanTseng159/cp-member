<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/30
 * Time: 上午 11:53
 */

namespace Ksd\Mediation\Parameter\Order;

use Ksd\Mediation\Parameter\BaseParameter;

class WriteoffParameter extends BaseParameter
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
        $this->merchantnumber = $request->input('merchantnumber');
        $this->ordernumber = $request->input('ordernumber');
        $this->amount = $request->input('amount');
        $this->paymenttype = $request->input('paymenttype');
        $this->serialnumber = $request->input('serialnumber');
        $this->writeoffnumber = $request->input('writeoffnumber');
        $this->timepaid = $request->input('timepaid');
        $this->tel = $request->input('tel');
        $this->hash = $request->input('hash');

    }

}