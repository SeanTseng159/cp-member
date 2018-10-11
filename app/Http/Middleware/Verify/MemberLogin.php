<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

use App\Services\MemberService;

class MemberLogin
{
    use ApiResponseHelper;
    use ValidatorHelper;

    private $memberService;

    public function __construct(MemberService $memberService)
    {
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
        $email = $request->input('email');
        $password = $request->input('password');
        $platform = $request->header('platform');

        if ($password) {
            if (!$this->VerifyPassword($password)) return $this->apiRespFailCode('A0035');
        }

        $member = $this->memberService->findOnly($email, $password);

        if (!$member) {
            return $this->apiRespFailCode('E0020');
        }

        if ($member->status == 0 || $member->isRegistered == 0) {
            return $this->apiRespFailCode('E0021');
        }

        $member = $this->memberService->generateToken($member, $platform);
        if (!$member) {
            return $this->apiRespFailCode('E0025');
        }

        $request->member = $member;

        return $next($request);
    }
}
