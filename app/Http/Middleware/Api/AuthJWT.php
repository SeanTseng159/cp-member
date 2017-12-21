<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

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
    private $memberService;

    public function __construct(JWTTokenService $jwtTokenSer, MemberService $memberService)
    {
        $this->jwtTokenSer = $jwtTokenSer;
        $this->memberService = $memberService;
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
            $tokenData = $this->jwtTokenSer->checkToken($token);
            if (!$tokenData) {
                return $this->apiRespFail('E0022', '無法驗證token');
            }

            //來源為app, 需檢查DB裡的token
            $platform = $request->header('platform');
            $member = $this->memberService->find($tokenData->id);
            $status = ($member) ? $member->status : 0;

            if ($platform === 'oauth') {
                $status = 1;
            }

            if ($status == 0) {
                return $this->apiRespFail('E0022', '會員驗證失效');
            }

            if ($platform === 'app' && $member->token != $token) {
                return $this->apiRespFail('E0022', '會員驗證失效');
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->apiRespFail('E0022', '無法驗證token');
        }
        return $next($request);
    }
}
