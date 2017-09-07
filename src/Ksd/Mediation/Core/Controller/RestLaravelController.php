<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 下午 4:28
 */

namespace Ksd\Mediation\Core\Controller;


use App\Http\Controllers\Controller;

class RestLaravelController extends Controller
{
    protected $result = [];

    public function success($data = NULL)
    {
        return $this->responseFormat($data);
    }

    public function failure($code, $message, $data = [], $httpCode = 200)
    {
        return $this->responseFormat($data, $code, $message, $httpCode);
    }

    public function responseFormat($data, $code = '00000', $message = 'success', $httpCode = 200)
    {
        $result = [
            'code' => $code,
            'message' => $message,
        ];

        foreach ($this->result as $key => $value) {
            $result[$key] = $value;
        }

        if (!empty($data)) {
            $result['data'] = $data;
        }
        return response()->json($result, $httpCode , [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'X-Requested-With, Authorization, Content-Type, Accept'
        ]);
    }

    public function putResult($key, $value)
    {
        $this->result[$key] = $value;
        return $this;
    }
}