<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/19
 * Time: 下午 02:16
 */

namespace Ksd\Mediation\Parameter\Product;


use Ksd\Mediation\Parameter\BaseParameter;

class SearchParameter extends  BaseParameter
{
    public function codeigniterRequest($input)
    {
        parent::codeigniterRequest($input);
        $this->source = $input->get('source');
    }

    public function laravelRequest($request= null)
    {
        parent::laravelRequest($request);
        $this->search= $request->input('search');


    }

}