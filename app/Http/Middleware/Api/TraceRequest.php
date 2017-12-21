<?php
namespace App\Http\Middleware\Api;

use Closure;
use Log;

/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/13
 * Time: 上午 11:34
 */
class TraceRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Log::info('*******');
        Log::info($request->method() . ' ' . $request->path());
        Log::info('content-type: '. $request->header('content-type'));
        Log::debug('auth: ' . $request->header('authorization'));
        Log::debug('body: ' . file_get_contents('php://input'));
        return $next($request);
    }
}