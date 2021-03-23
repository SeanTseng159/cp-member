<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/8
 * Time: 上午 10:00
 */

namespace Ksd\Mediation\Parameter\Checkout;


use Ksd\Mediation\Helper\AddressHelper;
use Ksd\Mediation\Helper\ObjectHelper;
use Ksd\Mediation\Parameter\BaseParameter;

class ConfirmParameter extends BaseParameter
{
    use ObjectHelper;
    use AddressHelper;

    public $device;
    public $source;
    private $isCheck;
    public $payment;
    public $billing;
    public $cartType;

    /**
     * 處理 laravel request
     * @param $request
     */
    public function laravelRequest($request)
    {
        $this->device = $request->input('device');
        $this->source = $request->input('source');
        $this->orderNo = $request->input('orderNo');
        $this->repay = $request->input('repay');
        $this->hasLinePayApp = $request->input('hasLinePayApp', false);
        $this->cartNumber= $request->input('cartNumber',1);
        $this->code= $request->input('code',null);
        $this->online_code= $request->input('online_code',null);
        
        $this->processParameters($request, 'payment');
        $this->processParameters($request, 'billing');
        $this->processParameters($request, 'shipment');
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
}
