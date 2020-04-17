<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/4
 * Time: 下午 2:27
 */

namespace Ksd\Mediation\Parameter\Cart;



use Ksd\Mediation\Parameter\BaseParameter;

class ProductParameterv3 extends BaseParameter
{

    private $cityPass;



    /**
     * laravel request 參數處理
     * @param $request
     */
    public function laravelRequest($request)
    {
        $this->request($request->all());
    }

    /**
     * 參數處理
     * @param $parameters
     */
    private function request($parameters)
    {
        $this->cityPass = [];
        if (!empty($parameters)) {
            array_push($this->cityPass, $parameters); 
        }
    }


    /**
     * 取得 city pass來源 購物車商品資訊
     * @return mixed
     */
    public function cityPass()
    {
        return $this->cityPass;
    }
}