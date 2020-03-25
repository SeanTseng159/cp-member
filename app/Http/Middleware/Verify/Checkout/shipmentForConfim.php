<?php

namespace App\Http\Middleware\Verify\Checkout;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;

class shipmentForConfim
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

        if($data['shipment']['id']==1){
            $validator = Validator::make($data, [
                'device' => 'required',
                'source' => 'required',
            ]);
        }else{
            $validator = Validator::make($data, [
                'device' => 'required',
                'source' => 'required',
                'shipment.id' => 'required',
                'shipment.userName' => 'required|min:2',
                'shipment.cellphone' => 'required|min:5',
                'shipment.address' => ['required', 'regex:/([號号])|([nN][oO])/']
            ]); 
        }

        if ($validator->fails()) {
            return $this->apiRespFail('E0001', join(' ', $validator->errors()->all()));
        }
        
        return $next($request);
    }
}
