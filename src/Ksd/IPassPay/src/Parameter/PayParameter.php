<?php
/**
 * Created by PhpStorm.
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午 2:27
 */

namespace Ksd\IPassPay\Parameter;

use Carbon;

class PayParameter
{
    public $token;
    public $platform;
    public $source;
    public $orderNo;
    public $orderId;
    public $id;
    public $itemId = '';

    private $bindPayReySignatureOptions = ['client_id', 'respond_type', 'version', 'lang_type', 'order_id', 'order_name', 'amount', 'success_url', 'failure_url', 'timestamp', 'client_pw'];
    private $bindPayTokenSignatureOptions = ['client_id', 'order_id', 'token', 'timestamp', 'client_pw'];
    private $bindPayStatusSignatureOptions = ['client_id', 'respond_type', 'version', 'order_id', 'token', 'timestamp', 'client_pw'];

    /**
     * laravel request 參數處理
     * @param $request
     */
    public function laravelRequest($request)
    {
        $this->token = $request->input('_token');
        $this->platform = $request->input('_platform');
        $this->source = $request->input('source');
        $this->orderNo = $this->id = $request->input('orderNo');
        $this->orderId = $this->id = $request->input('orderId');

        $request->session()->put('ipassPay', [
                'token' => $this->token,
                'platform' => $this->platform,
                'source' => $this->source,
                'orderNo' => $this->orderNo,
                'orderId' => $this->orderId
            ]);
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
        // $parameter->amount = $order[0]->orderAmount;
        $parameter->amount = 10;
        // $parameter->item_name = $this->itemsToItemString($order[0]->items);
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
