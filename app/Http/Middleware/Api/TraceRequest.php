<?php

namespace App\Http\Middleware\Api;

use App\Notifications\ApiResponseNotify;
use Closure;
use Illuminate\Notifications\Notifiable;
use Log;

/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/13
 * Time: 上午 11:34
 */
class TraceRequest
{
    use Notifiable;

    public function routeNotificationForSlack() {
        return env('LOG_SLACK_WEBHOOK_URL');
    }


    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::info('**************************************************************************************************');
        
        Log::info($request->method() . ' ' . $request->path());
        Log::info('content-type: ' . $request->header('content-type'));
        Log::debug('auth: ' . $request->header('authorization'));
        Log::debug('body: ' . file_get_contents('php://input'));
        $response = $next($request);
        $responseTime = (microtime(true) - LARAVEL_START);
        Log::debug('time: ' . $responseTime);

//        if ($responseTime > 1) {
//            $obj = new \stdClass();
//            $obj->method = $request->method();
//            $obj->api = $request->path();
//            $obj->responseTime = round($responseTime*1000,0);
//
//            $this->notify(new ApiResponseNotify($obj));
//
//        }
        return $response;

    }

}
