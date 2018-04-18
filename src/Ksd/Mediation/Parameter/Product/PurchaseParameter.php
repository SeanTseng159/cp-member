<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2018/04/18
 * Time: 下午 2:12
 */

namespace Ksd\Mediation\Parameter\Product;


use Ksd\Mediation\Parameter\BaseParameter;

class PurchaseParameter extends BaseParameter
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
    public function laravelRequest($id, $request = null)
    {
        parent::laravelRequest($request);
        $this->id = urldecode($id);
        $this->source = $request->input('source');
    }
}
