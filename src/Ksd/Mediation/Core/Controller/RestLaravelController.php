<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 下午 4:28
 */

namespace Ksd\Mediation\Core\Controller;


use App\Http\Controllers\Controller;
use App\Exceptions\ErrorCode;

class RestLaravelController extends Controller
{
    protected $result = [];

    /**
     * 回傳成功 json 訊息
     * @param null $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data = NULL)
    {
        return $this->responseFormat($data);
    }

    /**
     * 回傳失敗 json 訊息
     * @param $code
     * @param $message
     * @param array $data
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function failure($code, $message, $data = [], $httpCode = 200)
    {
        return $this->responseFormat($data, $code, $message, $httpCode);
    }

    /**
     * 回傳指定的失敗 json 訊息
     * @param $code
     * @param $message
     * @param array $data
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function failureCode($code, $data = [], $httpCode = 200)
    {
        return $this->responseFormat($data, $code, ErrorCode::message($code), $httpCode);
    }

    /**
     * 回傳 json 訊息
     * @param $data
     * @param string $code
     * @param string $message
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseFormat($data = null, $code = '00000', $message = 'success', $httpCode = 200)
    {
        $result = [
            'code' => $code,
            'message' => $message,
        ];

        foreach ($this->result as $key => $value) {
            $result[$key] = $value;
        }

        $result['data'] = $data;

        return response()->json($result, $httpCode , [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'X-Requested-With, Authorization, Content-Type, Accept'
        ]);
    }

    /**
     * 存放根目錄訊息
     * @param $key
     * @param $value
     * @return $this
     */
    public function putResult($key, $value)
    {
        $this->result[$key] = $value;
        return $this;
    }
}
