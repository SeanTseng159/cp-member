<?php

namespace App\Http\Middleware\Verify\Cart;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;

class Info
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

        $validator = Validator::make($data, [
            'action' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->apiRespFail('E0001', join(' ', $validator->errors()->all()));
        }

        return $next($request);
    }
}
