<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Http\Middleware\Api;

use App\Services\JWTTokenService;
use App\Traits\ApiResponseHelper;
use Closure;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminJWT
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
        $token = $request->bearerToken();
        //來源為app, 需檢查DB裡的token
        $platform = $request->header('platform');

        if (empty($token)) {
            return $this->apiRespFail('E0023', '無法取得Token');
        }

        if ($platform !== 'oauth') {
            return $this->apiRespFail('E0027', '授權方式錯誤');
        }

        try {
            $tokenData = $this->jwtTokenSer->checkToken($token);
            if (!$tokenData) {
                return $this->apiRespFail('E0022', '無法驗證token');
            }
        } catch (Exception $e) {
            return $this->apiRespFail('E0022', '無法驗證token');
        }
        return $next($request);
    }
}
