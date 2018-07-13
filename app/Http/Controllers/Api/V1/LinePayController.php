<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Payment\Services\LinePayService;

class LinePayController extends RestLaravelController
{
    public function __construct()
    {

    }

    /**
     * linepay 付款完成 callback, 更新訂單
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmCallback(Request $request)
    {
        \Log::debug('=== linepay confirm callback ===');
        \Log::debug(print_r($request->all(), true));

        $device = $request->input('device');
        
        (new LinePayService())->feedback($request->all());
        

        if ($device === 'app') {
            return "<script> location.href = 'LinePayTest://' </script>";
        }
        else {
            return redirect('https://dev.citypass.tw');
        }
    }
}
