<?php

namespace App\Http\Middleware\Verify\Checkout;

use Closure;
use Validator;
use Response;

use App\Traits\ApiResponseHelper;
use App\Traits\ValidatorHelper;

class Payment
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
            'action' => 'required',
            'payment.id' => 'required',
            'payment.type' => 'required',
            'shipment.id' => 'required|integer',
            'billing.id' => 'required|integer'
        ];

        if (!in_array($data['action'], ['buyNow', 'market'])) return $this->apiRespFailCode('E9019');

        // 信用卡參數
        if ($data['payment']['type'] === 'credit_card') {
            $validatorParams['payment.creditCardNumber'] = ['required', new \LVR\CreditCard\CardNumber];
            $validatorParams['payment.creditCardYear'] = 'required|date_format:"Y"';
            $validatorParams['payment.creditCardMonth'] = 'required|date_format:"m"';
            $validatorParams['payment.creditCardCode'] = ['required', new \LVR\CreditCard\CardCvc($data['payment']['creditCardNumber'])];
        }

        // 貨運參數
        if ($data['shipment']['id'] == 2) {
            $validatorParams['shipment.userName'] = 'required';
            $validatorParams['shipment.country'] = 'required';
            $validatorParams['shipment.countryCode'] = 'required';
            $validatorParams['shipment.cellphone'] = 'required';
            $validatorParams['shipment.zipcode'] = 'required|between:3,5';
            $validatorParams['shipment.address'] = ['required', 'regex:/([0-9]+[號号])|([nN][oO])/'];
        }

        // 發票參數
        if ($data['billing']['id'] == 2) {
            $validatorParams['billing.invoiceTitle'] = 'required';
            $validatorParams['billing.unifiedBusinessNo'] = 'required';
        }

        $validator = Validator::make($data, $validatorParams);

        if ($validator->fails()) {
            return $this->apiRespFail('E0001', join(' ', $validator->errors()->all()));
        }

        // 貨運參數，確認手機格式
        if ($data['shipment']['id'] == 2) {
            $phoneNumber = $this->VerifyPhoneNumber($data['shipment']['country'], $data['shipment']['countryCode'], $data['shipment']['cellphone']);
            if (!$phoneNumber) return $this->apiRespFailCode('E9018');
        }

        return $next($request);
    }
}
