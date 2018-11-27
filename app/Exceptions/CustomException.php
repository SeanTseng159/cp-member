<?php
/**
 * User: lee
 * Date: 2018/03/07
 * Time: 上午 9:30
 */

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
