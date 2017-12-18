<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/11/7
 * Time: 下午 03:22
 */

namespace Ksd\Mediation\Parameter\Checkout;
use Ksd\Mediation\Parameter\BaseParameter;

class CreditCardParameter extends BaseParameter
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

        $this->processParameters($request, 'verify3d');
        $this->processParameters($request, 'payment');
        $this->processParameters($request, 'billing');
    }

    /**
     * 處理 3D驗證 request 跟 session data
     * @param $request
     */
    public function mergeRequest($request, $session)
    {
        $this->source = $session['source'];
        $this->orderNo = $session['orderNo'];

        $this->payment = new \stdClass();
        $this->payment->id = $session['paymentId'];
        $this->payment->creditCardNumber = $session['cardNumber'];
        $this->payment->creditCardYear = $session['expYear'];
        $this->payment->creditCardMonth = $session['expMonth'];
        $this->payment->creditCardCode = $session['code'];

        $this->verify3d = new \stdClass();
        $this->verify3d->eci = $request['ECI'];
        $this->verify3d->cavv = $request['CAVV'];
        $this->verify3d->xid = $request['XID'];
        $this->verify3d->errorCode = $request['ErrorCode'];
        $this->verify3d->errorMessage = $request['ErrorMessage'];
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
