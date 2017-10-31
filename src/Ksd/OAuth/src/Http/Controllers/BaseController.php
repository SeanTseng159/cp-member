<?php
/**
 * User: Lee
 * Date: 2017/10/27
 * Time: 下午2:20
 */

namespace Ksd\OAuth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class BaseController extends Controller
{
    public function success($data)
    {
        return Response::json($data);
    }

    public function failure($errorCode = '', $msg = '')
    {
        return ($errorCode && $msg) ? Response::json(['code' => $errorCode, 'message' => $msg]) : Response::json(['message' => 'Not Found!'], 404);
    }

    public function postSuccess($data)
    {
        return view('oauth::responseSuccess', ['data' => $data]);
    }

    public function postFailure($errorCode = '', $msg = '')
    {
        return view('oauth::responseFailure', ['errorCode' => $errorCode, 'msg' => $msg]);
    }
}
