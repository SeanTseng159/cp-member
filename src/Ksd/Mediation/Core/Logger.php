<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/12/12
 * Time: 下午 1:33
 */

namespace Ksd\Mediation\Core;


use Illuminate\Support\Facades\Log;

class Logger
{
    public function alert($message, $context = [])
    {
        Log::alert($message, $context);
    }

    public function critical($message, $context = [])
    {
        Log::critical($message, $context);
    }

    public function error($message, $context = [])
    {
        Log::error($message, $context);
    }

    public function warning($message, $context = [])
    {
        Log::warning($message, $context);
    }

    public function notice($message, $context = [])
    {
        Log::notice($message, $context);
    }

    public function info($message, $context = [])
    {
        Log::info($message, $context);
    }

    public function debug($message, $context = [])
    {
        Log::debug($message, $context);
    }
}