<?php

namespace App\Http\Middleware\Verify\Order\Guest;

use Closure;
use Response;

use App\Traits\ApiResponseHelper;


class Detail
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
        $orderNo = $request->input('orderNo');
        if (!$orderNo) return $this->apiRespFailCode('E0001');

        return $next($request);
    }
}
