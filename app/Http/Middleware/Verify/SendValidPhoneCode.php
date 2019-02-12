<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

use App\Services\MemberService;

class SendValidPhoneCode
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
        $data = $request->all();

        $validatorParams['id'] = 'required';

        $member = $this->memberService->find($data['id']);
        if ($member && $member->country && $member->countryCode && $member->cellphone) {
            $data['country'] = $member->country;
            $data['countryCode'] = $member->countryCode;
            $data['cellphone'] = $member->cellphone;
        }
        else {
            $validatorParams['countryCode'] = 'required|max:6';
            $validatorParams['cellphone'] = 'required|alpha_num|max:12';
            $validatorParams['country'] = 'required';
        }


        $validator = Validator::make($data, $validatorParams);
        if ($validator->fails()) {
            return $this->apiRespFailCode('E0001');
        }

        // 確認手機格式
        if ($data['country'] || $data['countryCode'] || $data['cellphone']) {
            $phoneNumber = $this->VerifyPhoneNumber($data['country'], $data['countryCode'], $data['cellphone']);
            if (!$phoneNumber) return $this->apiRespFailCode('E0301');

            // 確認手機是否使用
            if ($this->memberService->checkPhoneIsUse($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone'])) {
                return $this->apiRespFailCode('A0031');
            }

            $request->phoneNumber = $phoneNumber;
        }

        return $next($request);
    }
}
