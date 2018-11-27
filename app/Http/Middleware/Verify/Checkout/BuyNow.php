<?php

namespace App\Http\Middleware\Verify\Checkout;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;

class BuyNow
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
            'productId' => 'required',
            'specId' => 'required',
            'specPriceId' => 'required',
            'quantity' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->apiRespFail('E0001', join(' ', $validator->errors()->all()));
        }

        return $next($request);
    }
}
