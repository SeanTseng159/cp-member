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

            // 確認手機是否使用
            if ($this->memberService->checkPhoneIsUseForUpdate($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone'])) {
                return $this->apiRespFailCode('A0031');
            }

            $request->phoneNumber = $phoneNumber;
        }

        return $next($request);
    }
}
