<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

class MemberRegisterCheck2
{
    use ApiResponseHelper;
    use ValidatorHelper;

    public function __construct()
    {

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
        $params = $request->only([
                            'country',
                            'countryCode',
                            'cellphone'
                        ]);

        $validator = Validator::make($params, [
            'country' => 'required',
            'countryCode' => 'required',
            'cellphone' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->apiRespFailCode('E0001');
        }

        // 確認手機格式
        $phoneNumber = $this->VerifyPhoneNumber($params['country'], $params['countryCode'], $params['cellphone']);
        if (!$phoneNumber) return $this->apiRespFailCode('E0301');

        $request->phoneNumber = $phoneNumber;

        return $next($request);
    }
}
