<?php
/**
 * User: lee
 * Date: 2020/07/09
 * Time: 上午 9:42
 */

namespace App\Http\Middleware\Api;

use App\Services\JWTTokenService;
use App\Traits\ApiResponseHelper;
use Closure;
use Illuminate\Support\Facades\Log;
use Exception;

class GuestJWT
{
    use ApiResponseHelper;

    private $jwtTokenSer;

    public function __construct(JWTTokenService $jwtTokenSer)
    {
        $this->jwtTokenSer = $jwtTokenSer;
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
        try {
            $token = $request->bearerToken();
            if (empty($token)) {
                return $this->apiRespFail('E0023', '無法取得Token');
            }

            $tokenData = $this->jwtTokenSer->checkToken($token);
            if (!$tokenData || $tokenData->id !== 0) {
                return $this->apiRespFail('E0022', '無法驗證token');
            }

            $request->platform = $request->header('platform');
            $request->token = $token;
        } catch (Exception $e) {
            Log::error($e);
            return $this->apiRespFail('E0022', '無法驗證token');
        }
        return $next($request);
    }
}
