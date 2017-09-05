<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/31
 * Time: 下午 2:12
 */

namespace Ksd\Mediation\Parameter\Product;


use Ksd\Mediation\Parameter\BaseParameter;

class QueryParameter extends BaseParameter
{
    public function codeigniterRequest($no, $input = null)
    {
        parent::codeigniterRequest($input);
        $this->no = $no;
        $this->source = $input->get('source');
    }

    public function laravelRequest($no, $request = null)
    {
        parent::laravelRequest($request);
        $this->no = $no;
        $this->source = $request->input('source');
    }
}