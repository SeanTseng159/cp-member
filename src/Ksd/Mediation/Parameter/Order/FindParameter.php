<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/13
 * Time: 下午 2:01
 */

namespace Ksd\Mediation\Parameter\Order;


use Ksd\Mediation\Parameter\BaseParameter;

class FindParameter extends BaseParameter
{
    public function laravelRequest($id, $request = null)
    {
        parent::laravelRequest($request);
        $this->source = $request->input('source');
        $this->id = $id;
        $this->itemId = $request->input('id');
    }

}