<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 05:18
 */

namespace App\Http\Controllers\Api;

//use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\NotificationService;

use Validator;

class NotificationController extends RestLaravelController
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function register(Request $request){


        $data = $request->only([
            'token',
            'platform',
            'memberId',
            //'deviceId',
            ]
        );

        $validator = Validator::make($data, [
            'token' => 'required',
            'platform' => 'required',
            //'deviceId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        if($this->notificationService->register($data)){
            return $this->success();
        }else{
            return $this->failure('E0002', '推播金鑰註冊失敗');
        }

    }

}