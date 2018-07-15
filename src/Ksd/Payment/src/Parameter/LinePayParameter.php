<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace Ksd\Payment\Parameter;

class LinePayParameter
{
    public function feedback($request)
    {
    	$parameters['code'] = $request->input('code');
    	$parameters['message'] = $request->input('message');
    	$parameters['data'] = $request->input('data');
    	$parameters['device'] = $request->input('device');

    	$parameters['record'] = [
            'order_no' => $parameters['data']['orderId'],
            'amount'   => $parameters['data']['amount'],
            'status'   => $parameters['code'] === '00000' ? 1 : 0,
            'transactionId' => $parameters['data']['transactionId'],
            'device'   => $parameters['device'],
        ];

    	return $parameters;
    }
}
