<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/11/7
 * Time: 下午 03:22
 */

namespace Ksd\Mediation\Parameter\Checkout;
use Ksd\Mediation\Parameter\BaseParameter;

class TransmitParameter extends BaseParameter
{
    public $source;
    public $orderNo;
    private $isCheck;
    public $payment;
    public $billing;
    public $verify3d;



    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($request)
    {
        $this->source = $request->input('source');
        $this->orderNo = $request->input('orderNo');


        $this->processParameters($request, 'payment');
        $this->processParameters($request, 'billing');
    }



    /**
     * 處理參數
     * @param $request
     * @param $property
     */
    public function processParameters($request, $property)
    {
        $paymentParameters = $request->input($property);
        $this->$property = new \stdClass();
        if (!empty($paymentParameters)) {
            foreach ($paymentParameters as $key => $value) {
                $this->$property->$key = $value;
            }
        }
    }

    /**
     * 判斷來源
     * @param null $source
     * @return bool
     */
    public function checkSource($source = null)
    {
        $this->isCheck = $source === $this->source;
        return $this->isCheck;
    }

    /**
     * 取得付款資訊
     * @return mixed
     */
    public function payment()
    {
        return $this->payment;
    }

    /**
     * 取得帳單資訊
     * @return mixed
     */
    public function billing()
    {
        return $this->billing;
    }

    /**
     * 取得3D驗證資訊
     * @return mixed
     */
    public function verify3d()
    {
        return $this->verify3d;
    }
}
