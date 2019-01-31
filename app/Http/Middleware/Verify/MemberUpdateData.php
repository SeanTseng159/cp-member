<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

use App\Services\MemberService;

class MemberUpdateData
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
        $id = $request->id;
        $member = $this->memberService->find($id);

        if (!$member) return $this->apiRespFailCode('E0061');

        $email = $request->input('email');
        if ($email && !$this->verifyEmail($email)) return $this->apiRespFailCode('A0036');

        $isTw = $request->input('isTw');
        $socialId = $request->input('socialId');

        // 確認身分證格式
        if ($isTw && $socialId) {
            if (!$this->checkPid($socialId)) return $this->apiRespFailCode('A0034');
        }
        // 確認身分證/護照是否使用
        if ($socialId && $socialId !== $member->socialId && $this->memberService->checkSocialIdIsUse($socialId)) {
            return $this->apiRespFailCode('A0033');
        }

        $country = $request->input('country');
        $countryCode = $request->input('countryCode');
        $cellphone = $request->input('cellphone');

        // 確認手機格式
        if ($countryCode || $cellphone || $country) {
            $phoneNumber = $this->VerifyPhoneNumber($country, $countryCode, $cellphone);
            if (!$phoneNumber) return $this->apiRespFailCode('E0301');

            // 確認手機是否跟原本的相同
            $checkPhoneIsNotSame = ($phoneNumber['country'] != $member->country || $phoneNumber['countryCode'] != $member->countryCode || $phoneNumber['cellphone'] != $member->cellphone);

            $checkCellphone = $request->input('checkCellphone') ?: 'true';

            // 是否需判斷手機已使用
            if ($checkCellphone === 'true' && $checkPhoneIsNotSame) {
                // 確認手機是否使用
                if ($this->memberService->checkPhoneIsUse($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone'])) {
                    return $this->apiRespFailCode('A0031');
                }
            }

            $request->phoneNumber = $phoneNumber;
        }

        $password = $request->input('password');
        if ($password) {
            if (!$this->VerifyPassword($password)) return $this->apiRespFailCode('A0035');
        }

        return $next($request);
    }
}
