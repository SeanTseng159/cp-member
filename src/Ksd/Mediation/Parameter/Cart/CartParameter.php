<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2018/1/19
 * Time: 下午 03:38
 */

namespace Ksd\Mediation\Parameter\Cart;

use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Parameter\BaseParameter;

class CartParameter extends BaseParameter
{
    /**
     * 處理 ci request
     * @param $input
     */
    public function codeigniterRequest($request = null)
    {
        parent::codeigniterRequest($request);
        $this->source = $request->input('source');

    }


    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($request)
    {
        parent::laravelRequest($request);
        $this->source = $request->input('source');

    }
}