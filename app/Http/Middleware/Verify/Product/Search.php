<?php

namespace App\Http\Middleware\Verify\Product;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;

class Search
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
        if (!$request->input('search')) {
            return $this->apiRespFailCode('E0001');
        }

        return $next($request);
    }
}
