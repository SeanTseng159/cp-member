<?php

namespace App\Http\Requests\Api\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class Shipment extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'shipment.userAddress' => ['required','regex:/(..+[\x{7e23}|\x{5e02}])/uU']
        ];
    }


    public function messages()
    {
        return [
            'shipment.userAddress.required' => '請填寫地址',
            'shipment.userAddress.regex' => '地址格式不符合',
        ];
    }
}
