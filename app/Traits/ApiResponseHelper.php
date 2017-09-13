<?php

namespace App\Traits;

use Log;

trait ApiResponseHelper
{
    private $successCode = '0000';

    public function apiRespSuccess($data = [])
    {
        return $this->apiRespDetail($this->successCode, 'success', $data);
    }

    public function apiRespFail($code, $message, $data = [])
    {
        return $this->apiRespDetail($code, $message, $data);
    }

    public function apiRespDetail($code, $message, $data = [])
    {
        $resp = ['code' => $code, 'message' => $message];
        if (!empty($data)) {
            $resp['data'] = $data;
        }
        return response()->json($resp);
    }
}