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

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
                'url',
                'platform',
                'memberId',
                'sendtime',
            ]
        );

        $validator = Validator::make($data, [
            'title' => 'required',
            'body' => 'required',
            'url' => 'required',
            'platform' => 'required',
            'sendtime' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        //指定用戶推播
        //缺少用戶資料
        if($data['platform'] === '3' && !array_key_exists('memberId',$data)){
            return $this->failure('E0001', '傳送參數錯誤');
        }

        $time = date("Y-m-d H:i:00", strtotime("-3 minute"));
        //$time = date("Y-m-d H:i:s");

        //var_dump($time);

        //立刻送出
        //發送時間比接收時間略早
        if($data['sendtime'] < date("Y-m-d H:i:s") && $data['sendtime'] > $time){
            $this->notificationService->send($data);
        }

        //指定用戶測試
        if($data['platform']=='3'&&!empty($data['memberId'])){
            $this->notificationService->send($data);
        }

        /*
        $schedule = new Schedule();
        */



        /*
        $schedule->call(function($data){
            //$checktime = date("Y-m-d H:i:00");
            //if($time == $checktime){
            $this->notificationService->send($data);

            //}
        })->everyMinute()
          ->when(function($time){
              $checktime = date("Y-m-d H:i:00");
              if($time == $checktime){
                  return true;
              }else{
                  return false;
              }
          });
        */


        /*
        if($this->notificationService->send($data)){
            //return $this->success();
        }else{
            return $this->failure('E0052', '推播訊息發送失敗');
        }
        */

    }

}