<?php

/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/30
 * Time: 下午 02:19
 */

namespace Ksd\Mediation\Parameter\Layout;
use Ksd\Mediation\Parameter\BaseParameter;

class LayoutParameter extends  BaseParameter
{
    /**
     * 處理 ci request
     * @param $input
     */
    public function codeigniterRequest($no, $input = null)
    {
        parent::codeigniterRequest($input);
        $this->no = $no;

    }


    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($id, $request = null)
    {
        parent::laravelRequest($request);
        $this->id = $id;
    }
}