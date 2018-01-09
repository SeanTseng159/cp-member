<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2018/1/9
 * Time: 下午 2:08
 */

namespace App\Exceptions\Api\Checkout;


use Throwable;

class PayCreditCardFailException extends \Exception
{
    public function __construct($message = "信用卡付款失敗", $code = 'C0301', Throwable $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
    }

}