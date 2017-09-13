<?php

namespace App\Http\Middleware\Api;

use App\Services\JWTTokenService;
use App\Services\MemberService;
use App\Traits\ApiResponseHelper;
use Closure;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthJWT
{
    use ApiResponseHelper;

    private $jwtTokenSer;
    private $memberTokenService;

    public function __construct(JWTTokenService $jwtTokenSer, MemberService $memberTokenService)
    {
        $this->jwtTokenSer = $jwtTokenSer;
        $this->memberTokenService = $memberTokenService;
    }

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
        if (empty($token)) {
            return $this->apiRespFail('A01001', '無效token');
        }

        try {
            $member = $this->memberTokenService->findByToken($token);
            if(empty($member)) {
                return $this->apiRespFail('A01004', '無法驗證金鑰');
            }

            $result = $this->jwtTokenSer->checkToken($token);
            if (!$result) {
                return $this->apiRespFail('A01002', '無法驗證token');
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->apiRespFail('A01002', '無法驗證token');
        }
        return $next($request);
    }
}
