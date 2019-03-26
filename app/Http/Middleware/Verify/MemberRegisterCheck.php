<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;

use App\Parameter\MemberParameter;

class MemberRegisterCheck
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
        $data = (new MemberParameter)->registerCheck($request);

        $validator = Validator::make($data, [
            'country' => 'required',
            'mobile' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->apiRespFailCode('E0001');
        }

        return $next($request);
    }
}
