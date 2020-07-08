<?php
/**
 * User: lee
 * Date: 2019/02/27
 * Time: 上午 9:42
 */

namespace App\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\JWTTokenService;


class GuestController extends RestLaravelController
{
    // protected $lang;

    public function __construct()
    {
        // $this->lang = env('APP_LANG');
    }

    /**
     * 登入
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request, JWTTokenService $jwtTokenService)
    {
        // ID 0 代表訪客
        $member = new \stdClass;
        $member->id = 0;

        $token = $jwtTokenService->generateToken($member, 'app');

        return $this->success([
            'token' => $token,
        ]);
    }
}
