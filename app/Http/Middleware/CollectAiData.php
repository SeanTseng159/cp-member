<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Session;
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
        $token = $request->bearerToken();
        $tokenData = (new JWTTokenService())->checkToken($token);
        
        if ( ! App::environment('production')) return $next($request);
        $data = [
                    'act' => 'view',
                    'url' =>  url()->current(),
                    'user' => $tokenData->id ?? NULL,
                    'agent' => $request->header('User-Agent'),
                    'preurl' => url()->previous(),
                    'site' => 'CityPass',
                    'lang' => $request->server('HTTP_ACCEPT_LANGUAGE'),
                    'client_ip' => $request->ip(),
                    'platform' => 'api',
                ];
        CollectAiDataJob::dispatch($data);
        return $next($request);
    }
}
