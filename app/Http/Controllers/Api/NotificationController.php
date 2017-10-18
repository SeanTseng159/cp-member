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

    //註冊裝置推播金鑰
    public function register(Request $request){


        $data = $request->only([
            'token',
            'platform',
            'memberId',
            ]
        );

        $validator = Validator::make($data, [
            'token' => 'required',
            'platform' => 'required',
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

    //發送推播訊系
    public function send(Request $request){

        $data = $request->only([
                'title',
                'body',
                'type',
                'url',
                'platform',
                'memberId',
            ]
        );

        $validator = Validator::make($data, [
            'title' => 'required',
            'body' => 'required',
            'type' => 'required',
            'url' => 'required',
            'platform' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        //指定用戶推播
        //缺少用戶資料
        if($data['platform'] === '3' && !array_key_exists('memberId',$data)){
            return $this->failure('E0001', '傳送參數錯誤');
        }


        if($this->notificationService->send($data)){
            //return $this->success();
        }else{
            return $this->failure('E0052', '推播訊息發送失敗');
        }


    }

}