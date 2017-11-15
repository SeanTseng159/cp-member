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
    public function laravelRequest($id, $request = null)
    {
        $this->source = $request->input('source');
        $this->id = $request->input('id');
        $this->payMethod = $request->input('payMethod');
        $this->payResult = $request->input('payResult');
    }

}