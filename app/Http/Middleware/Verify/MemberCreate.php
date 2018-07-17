<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

use App\Services\MemberService;
use App\Parameter\MemberParameter;

class MemberCreate
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
        $data = (new MemberParameter)->create($request);

        $validator = Validator::make($data, [
            'countryCode' => 'required|max:6',
            'cellphone' => 'required|alpha_num|max:12',
            'openPlateform' => 'required',
            'country' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->apiRespFailCode('E0001');
        }

        // 確認手機格式
        if ($data['country'] || $data['countryCode'] || $data['cellphone']) {
            $phoneNumber = $this->VerifyPhoneNumber($data['country'], $data['countryCode'], $data['cellphone']);
            if (!$phoneNumber) return $this->apiRespFailCode('E0301');

            // 確認手機是否使用
            if ($this->memberService->checkHasPhoneAndNotRegistered($phoneNumber['country'], $phoneNumber['countryCode'], $phoneNumber['cellphone'])) {
                return $this->apiRespFailCode('A0031');
            }

            $request->phoneNumber = $phoneNumber;
        }

        return $next($request);
    }
}
