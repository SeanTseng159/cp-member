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

    //發送推播訊息
    public function send(Request $request){

        $data = $request->only([
                'id',
                'delete',
                'title',
                'body',
                'type',
                'url',
                'platform',
                'sendtime',
                'memberId',
                'status',
                'modifier',
            ]
        );

        $validator = Validator::make($data, [
            'title' => 'required',
            'body' => 'required',
            'url' => 'required',
            'platform' => 'required',
            'sendtime' => 'required',
            'status' => 'required',
            'modifier' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->failure('E0001', '傳送參數錯誤');
        }

        //測試指定用戶推播
        //缺少用戶資料
        if($data['platform'] === '3' && !array_key_exists('memberId',$data)){
            return $this->failure('E0001', '傳送參數錯誤');
        }

        if($data['platform'] === '3' && array_key_exists('memberId',$data)){
            $this->notificationService->send($data);
            return $this->success(['data'=>$data]);
            exit;
        }


        //推播資料寫入資料庫
        if(array_key_exists('id',$data)){
            //更新
            $id = $this->notificationService->updateMessage($data);
            if(!is_null($id)){
                return $this->success(['id' => $id]);
            }else{
                return $this->failure('E0001', '訊息id不存在');
            }

        }else{
            //新增
            $id = $this->notificationService->createMessage($data);
            return $this->success(['id' => $id]);
        }


    }

    //所有推播訊息
    public function allMessage(Request $request){

        $messages = $this->notificationService->allMessage();

        return $this->success($messages);
    }

    //查詢推播訊息內容
    public function queryMessage($id){

        $message = $this->notificationService->queryMessage($id);

        if(!is_null($message)){
            return $this->success($message);
        }else{
            return $this->failure('E0001', '訊息id不存在');
        }
    }

}