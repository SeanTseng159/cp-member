<?php

/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 03:19
 */

namespace Ksd\Mediation\Parameter\Order;

use Ksd\Mediation\Parameter\BaseParameter;

class OrderParameter extends BaseParameter
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
    public function laravelRequest($itemId, $request = null)
    {
        parent::laravelRequest($request);
        $this->itemId = $itemId;
        $this->id = $request->input('id');
        $this->source = $request->input('source');
    }

}