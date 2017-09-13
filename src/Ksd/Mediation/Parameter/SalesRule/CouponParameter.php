<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/12
 * Time: 下午 5:56
 */

namespace Ksd\Mediation\Parameter\SalesRule;


use Ksd\Mediation\Parameter\BaseParameter;

class CouponParameter extends BaseParameter
{

    public function laravelRequest($request)
    {
        parent::laravelRequest($request);
        $this->source = $request->input('source');
        $this->code = $request->input('code');
    }
}