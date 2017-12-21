<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/8/31
 * Time: 下午 2:11
 */

namespace Ksd\Mediation\Parameter\Product;


use Ksd\Mediation\Parameter\BaseParameter;
use Ksd\Mediation\Traits\Product\Sort;

class TagsParameter extends BaseParameter
{
    use Sort;

    /**
     * 處理 ci request
     * @param $input
     */
    public function codeigniterRequest($input)
    {
        parent::codeigniterRequest($input);
        $this->names = $input->get('names');
    }

    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($request)
    {
        parent::laravelRequest($request);
        $this->names = $request->input('names');
    }

    /**
     * 取得分類陣列
     * @return mixed
     */
    public function categories()
    {
        return $this->names;
    }
}