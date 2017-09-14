<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/13
 * Time: 下午 05:46
 */

namespace Ksd\Mediation\Parameter\Order;

use Ksd\Mediation\Parameter\BaseParameter;


class SearchParameter   extends  BaseParameter
{
    public function codeigniterRequest($input)
    {
        parent::codeigniterRequest($input);
        $this->source = $input->get('source');
    }

    public function laravelRequest($request= null)
    {
        parent::laravelRequest($request);
        $this->status= $request->input('status');
        $this->orderNo = $request->input('orderNo');
        $this->name = $request->input('name');
        $this->initDate = $request->input('initDate');
        $this->endDate = $request->input('endDate');

    }



}