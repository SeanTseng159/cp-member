<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;
use App\Rules\Recaptcha;

use App\Traits\ApiResponseHelper;

class PartnerJoin
{
    use ApiResponseHelper;

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
        $data = $request->all();

        $validatorData = [
                'company' => 'required',
                'contactWindow' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'message' => 'max:255'
            ];

        if ($request->has('recaptchaToken')) {
            $validatorData['recaptchaToken'] = ['required', new Recaptcha];
        }

        $validator = Validator::make($data, $validatorData);

        if ($validator->fails()) {
            return $this->apiRespFail('E0001', join(' ', $validator->errors()->all()));
        }

        return $next($request);
    }
}
