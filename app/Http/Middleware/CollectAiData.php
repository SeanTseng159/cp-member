<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\JWTTokenService;
use App\Services\MemberService;
use App\Jobs\CollectAiData as CollectAiDataJob;
use GuzzleHttp\Client as CollectionClient;

class CollectAiData
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
        $start = microtime(true);
        $response = $next($request);
        $end = microtime(true);
        $excute_time = ($end - $start) * 1000;
        $data = [
                    'act' => 'click',
                    'url' =>  url()->current(),
                    'user' => $request->memberId,
                    'agent' => $request->header('User-Agent'),
                    'preurl' => url()->previous(),
                    'page_view_time' => $excute_time,
                    'site' => 'citypass',
                    'lang' => $request->server('HTTP_ACCEPT_LANGUAGE'),
                    'client_ip' => $request->ip(),
                ];
        CollectAiDataJob::dispatch($data);
        return $response;
    }
}
