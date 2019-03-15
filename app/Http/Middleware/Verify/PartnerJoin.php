<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

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
        $data = $request->only([
                    'company',
                    'contact_window',
                    'phone',
                    'email',
                    'message'
                ]);

        $validator = Validator::make($data, [
            'company' => 'required',
            'contact_window' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
            'message' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return $this->apiRespFail('E0001', join(' ', $validator->errors()->all()));
        }

        return $next($request);
    }
}
