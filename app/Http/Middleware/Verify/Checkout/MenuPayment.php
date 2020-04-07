<?php

namespace App\Http\Middleware\Verify\Checkout;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

class MenuPayment
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
        $data = $request->all();

        $validatorParams = [
            'device' => 'required',
            'payment.id' => 'required',
            'payment.type' => 'required',
            'billing.id' => 'required|integer'
        ];

        //綠界後不用信用卡參數
        // 信用卡參數
        // if ($data['payment']['type'] === 'credit_card') {
        //     $validatorParams['payment.creditCardNumber'] = ['required', new \LVR\CreditCard\CardNumber];
        //     $validatorParams['payment.creditCardYear'] = 'required|date_format:"Y"';
        //     $validatorParams['payment.creditCardMonth'] = 'required|date_format:"m"';
        //     $validatorParams['payment.creditCardCode'] = ['required', new \LVR\CreditCard\CardCvc($data['payment']['creditCardNumber'])];
        // }

        // 發票參數
        if ($data['billing']['id'] == 2) {
            $validatorParams['billing.invoiceTitle'] = 'required';
            $validatorParams['billing.unifiedBusinessNo'] = 'required';
        }

        $validator = Validator::make($data, $validatorParams);

        if ($validator->fails()) {
            return $this->apiRespFail('E0001', join(' ', $validator->errors()->all()));
        }
        return $next($request);
    }
}
