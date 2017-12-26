<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/10/24
 * Time: 上午 09:54
 */

namespace Ksd\Mediation\Parameter\Wishlist;

use Ksd\Mediation\Parameter\BaseParameter;

class AllParameter extends BaseParameter
{
    /**
     * 處理 ci request
     * @param $input
     */
    public function codeigniterRequest($input)
    {
        parent::codeigniterRequest($input);

    }

    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($no, $request = null)
    {
        parent::laravelRequest($request);
        $this->no = $no;
        $this->source = $request->input('source');


    }
}