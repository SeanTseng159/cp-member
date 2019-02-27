<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

use App\Services\MemberService;
use App\Parameter\MemberParameter;

class MemberRegisterByDiningCar
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
        $data = (new MemberParameter)->registerByDiningCar($request);

        $validatorData = [
            'countryCode' => 'required|max:6',
            'cellphone' => 'required|alpha_num|max:12',
            'openPlateform' => 'required',
            'country' => 'required',
            'openPlateform' => 'required',
            'name' => 'required'
        ];

        if ($data['openPlateform'] === 'citypass') {
            $validatorData['email'] = 'required|email';
        }
        else {
            $validatorData['openId'] = 'required|email';
        }


        $validator = Validator::make($data, $validatorData);

        if ($validator->fails()) {
            return $this->apiRespFailCode('E0001');
        }

        // 確認手機格式
        if ($data['country'] || $data['countryCode'] || $data['cellphone']) {
            $phoneNumber = $this->VerifyPhoneNumber($data['country'], $data['countryCode'], $data['cellphone']);
            if (!$phoneNumber) return $this->apiRespFailCode('E0301');

            // 確認手機是否使用
            if ($this->memberService->checkHasPhoneAndisRegistered($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone'])) {
                return $this->apiRespFailCode('A0031');
            }

            $request->phoneNumber = $phoneNumber;
        }

        // 檢查Email是否使用
        if ($data['openPlateform'] === 'citypass') {
            if ($this->memberService->checkEmailIsUse($data['email'])) {
                return $this->apiRespFailCode('A0032');
            }
        }
        else {
            if ($this->memberService->findByOpenId($data['openId'], $data['openPlateform'])) {
                return $this->apiRespFailCode('E0015');
            }
        }

        return $next($request);
    }
}
