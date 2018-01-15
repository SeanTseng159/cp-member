<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2018/01/14
 * Time: 下午 2:27
 */

namespace Ksd\IPassPay\Parameter;

use App\Services\JWTTokenService;

class LogParameter
{
    /**
     * 送付款
     * @param $request
     */
    public function pay($request)
    {
        $token = $request->input('_token');
        $platform = $request->input('_platform');
        $source = $request->input('source');
        $orderNo = $this->id = $request->input('orderNo');
        $orderId = $request->input('orderId');

        $memberData = (new JWTTokenService)->checkToken($token);

        return [
            'member_id' => $memberData->id,
            'order_no' => $orderNo,
            'order_id' => $orderId,
            'source' => $source,
            'platform' => $platform
        ];
    }

    /**
     * EC平台請求支付Token (步驟一)
     * @param $request
     */
    public function bindPayReq($order)
    {
        $parameter = new \stdClass;
        $parameter->client_id = env('IPASS_PAY_CLIENT_ID');
        $parameter->client_pw = env('IPASS_PAY_CLIENT_PW');
        $parameter->respond_type = env('IPASS_PAY_RESPOND_TYPE', 'json');
        $parameter->version = env('IPASS_PAY_VERSION', '1.0');
        $parameter->lang_type = 'zh-tw';
        $parameter->order_id = $this->orderId;
        $parameter->order_name = $this->orderId;
        $parameter->amount = $order[0]->orderAmount;
        $parameter->item_name = $this->itemsToItemString($order[0]->items);
        $parameter->item_name = '';
        $parameter->success_url = url('ipass/successCallback');
        $parameter->failure_url = url('ipass/failureCallback');
        $parameter->timestamp = Carbon\Carbon::now()->timestamp;
        $parameter->payment_company = 'iPASSPAY';
        $parameter->signature = '';

        foreach ($this->bindPayReySignatureOptions as $key) {
            $parameter->signature .= $parameter->{$key};
        }

        $parameter->signature = hash('sha256', $parameter->signature);

        unset($parameter->client_pw);

        return $parameter;
    }

    /**
     * EC平台請求支付Token (步驟二)
     * @param $data
     * @param $request
     */
    public function bindPayToken($data)
    {
        $parameter = new \stdClass;
        $parameter->client_id = env('IPASS_PAY_CLIENT_ID');
        $parameter->client_pw = env('IPASS_PAY_CLIENT_PW');
        $parameter->order_id = $data->order_id;
        $parameter->token = $data->token;
        $parameter->timestamp = $data->timestamp;
        $parameter->signature = '';

        foreach ($this->bindPayTokenSignatureOptions as $key) {
            $parameter->signature .= $parameter->{$key};
        }

        $parameter->signature = hash('sha256', $parameter->signature);

        unset($parameter->client_pw);

        return $parameter;
    }

    /**
     * 支付確認 (最後步驟)
     * @param $data
     */
    public function bindPayStatus($callback)
    {
        if ($callback) {
            $parameter = $callback;
            $parameter->client_pw = env('IPASS_PAY_CLIENT_PW');
            $parameter->respond_type = env('IPASS_PAY_RESPOND_TYPE', 'json');
            $parameter->version = env('IPASS_PAY_VERSION', '1.0');
            $parameter->signature = '';

            foreach ($this->bindPayStatusSignatureOptions as $key) {
                $parameter->signature .= $parameter->{$key};
            }

            $parameter->signature = hash('sha256', $parameter->signature);

            unset($parameter->client_pw);

            return $parameter;
        }

        return null;
    }

    /**
     * 退款
     * @param $data
     */
    public function bindRefund($request)
    {
        $order_id = $request->input('order_id');
        $amt = $request->input('amt');

        if ($order_id && $amt) {
            $parameter = new \stdClass;
            $parameter->client_id = env('IPASS_PAY_CLIENT_ID');
            $parameter->client_pw = env('IPASS_PAY_CLIENT_PW');
            $parameter->respond_type = env('IPASS_PAY_RESPOND_TYPE', 'json');
            $parameter->version = env('IPASS_PAY_VERSION', '1.0');
            $parameter->order_id = $order_id;
            $parameter->amt = $amt;
            $parameter->timestamp = Carbon\Carbon::now()->timestamp;
            $parameter->signature = '';

            foreach ($this->bindRefundOptions as $key) {
                $parameter->signature .= $parameter->{$key};
            }

            $parameter->signature = hash('sha256', $parameter->signature);

            unset($parameter->client_pw);

            return $parameter;
        }

        return null;
    }

    private function itemsToItemString($items)
    {
        $ary = [];
        foreach ($items as $key => $item) {
            $ary[] = $item['name'] . ' ' . $item['price'] . ' 元 X' . $item['quantity'];
        }

        return implode('|', $ary);
    }
}
