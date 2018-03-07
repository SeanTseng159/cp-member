<?php
/**
 * User: lee
 * Date: 2018/03/05
 * Time: 上午 9:42
 */

namespace App\Parameter\Magento;

class ProductParameter
{
    /**
     * laravel request 參數處理
     * @param $request
     */
    public function all($request)
    {
        $parameter = new \stdClass;
        $parameter->type = $request->input('type');
        $parameter->page = $request->input('page');
        $parameter->limit = $request->input('limit');

        return $parameter;
    }

    /**
     * laravel request 參數處理
     * @param $request
     */
    public function query($request)
    {
        $parameter = new \stdClass;
        $parameter->products = $request->input('products');

        return $parameter;
    }
}
