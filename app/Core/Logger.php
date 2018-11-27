<?php
/**
 * User: lee
 * Date: 2018/05/10
 * Time: 下午 1:33
 */

namespace App\Core;

use Illuminate\Support\Facades\Log;

class Logger
{
    public static function alert($message, $context = [])
    {
        Log::alert($message);
        Log::alert(print_r($context, true));
    }

    public static function critical($message, $context = [])
    {
        Log::critical($message);
        Log::critical(print_r($context, true));
    }

    public static function error($message, $context = [])
    {
        Log::error($message);
        Log::error(print_r($context, true));
    }

    public static function warning($message, $context = [])
    {
        Log::warning($message);
        Log::warning(print_r($context, true));
    }

    public static function notice($message, $context = [])
    {
        Log::notice($message);
        Log::notice(print_r($context, true));
    }

    public static function info($message, $context = [])
    {
        Log::info($message);
        Log::info(print_r($context, true));
    }

    public static function debug($message, $context = [])
    {
        Log::debug($message);
        Log::debug(print_r($context, true));
    }
}
