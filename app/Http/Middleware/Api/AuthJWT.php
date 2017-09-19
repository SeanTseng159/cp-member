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
            return $this->apiRespFail('E0023', '無法取得Token');
        }

        try {
            $result = $this->jwtTokenSer->checkToken($token);
            if (!$result) {
                return $this->apiRespFail('E0022', '無法驗證token');
            }

            var_dump($result);

            /*$member = $this->memberTokenService->findByToken($token);
            if ($member->status == 0) {
                return $this->apiRespFail('E0022', '無法驗證token');
            }*/

            //來源為app, 需檢查DB裡的token
            $platform = $request->header('platform');
            if ($platform === 'app') {
                $member = $this->memberTokenService->findByToken($token);
                if(empty($member) || $member->status == 0) {
                    return $this->apiRespFail('E0022', '會員驗證失效');
                }
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->apiRespFail('E0022', '無法驗證token');
        }
        return $next($request);
    }
}
