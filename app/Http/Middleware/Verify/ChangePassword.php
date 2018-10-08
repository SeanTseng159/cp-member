<?php

namespace App\Http\Middleware\Verify;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

class ChangePassword
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
        $password = $request->input('password');
        $oldpassword = $request->input('oldpassword');

        if (!$password || !$oldpassword) {
            return $this->apiRespFailCode('E0006');
        }

        if (!$this->VerifyPassword($password) || !$this->VerifyPassword($oldpassword)) {
            return $this->apiRespFailCode('A0035');
        }

        return $next($request);
    }
}
