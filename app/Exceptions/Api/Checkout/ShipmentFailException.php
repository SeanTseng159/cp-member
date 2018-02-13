<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2018/2/13
 * Time: 下午 03:55
 */

namespace App\Exceptions\Api\Checkout;

use Throwable;

class ShipmentFailException extends \Exception
{
    public function __construct($message = "部分商品已下架", $code = 'E9009', Throwable $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
    }


}