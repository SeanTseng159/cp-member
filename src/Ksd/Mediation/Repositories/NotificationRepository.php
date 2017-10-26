<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 06:01
 */

namespace Ksd\Mediation\Repositories;

use App\Models\NotificationMobile;
use App\Models\Notification;

use Illuminate\Database\QueryException;

class NotificationRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    //註冊裝置推播金鑰
    public function register($parameter){
        try{
            $notimob = new NotificationMobile();

            $old_registed = $notimob->where([
                ['platform', '=', $parameter['platform']],
                ['mobile_token', '=', $parameter['token']]
            ])
            ->get();

            if($old_registed->count() == 0){

                    $notimob->mobile_token = $parameter['token'];
                    $notimob->platform = $parameter['platform'];
                    if(array_key_exists('memberId',$parameter)){
                        $notimob->member_id = $parameter['memberId'];
                    }
                    //$notimob->device_id = $parameter['deviceId'];
                    $notimob->save();
                return $notimob;
            }else{
                $update_record = $old_registed->first();
                if(array_key_exists('memberId',$parameter)){
                    $update_record->member_id = $parameter['memberId'];
                    $update_record->save();
                }

                return true;
            }

        }catch(QueryException $e){

            return false;
        }
    }

    //取得平台裝置註冊金鑰
    public function devicesByPlatform($platform){

        $notimob = new NotificationMobile();

        $devices = $notimob->where([
            ['platform', '=', $platform],
        ])
            ->get();

        return $devices;
    }


    //取得使用者註冊金鑰
    public function devicesByMember($member){

        $notimob = new NotificationMobile();

        $devices = $notimob->where([
            ['member_id', '=', $member],
        ])
            ->get();

        return $devices;
    }

    //刪除token
    public function deleteByToken($token){
        $notimob = new NotificationMobile();

        $notimob->where([
            ['mobile_token', '=', $token],
        ])
            ->delete();

    }

    //刪除toekn-platform
    public function deleteByTokenPlatform($token, $platform){
        $notimob = new NotificationMobile();

        $notimob->where([
            ['mobile_token', '=', $token],
            ['platform', '=', $platform],
        ])
            ->delete();
    }


    //新增推播訊錫
    public function createMessage($data){
        $notification = new Notification();

        $notification->title = $data['title'];
        $notification->body = $data['body'];
        if(array_key_exists('type',$data)){
            $notification->type = $data['type'];
        }else{
            $notification->type = 0;
        }

        $notification->sent = 0;
        $notification->url = $data['url'];
        $notification->platform = $data['platform'];
        $notification->time = $data['sendtime'];
        $notification->status = $data['status'];
        $notification->modifier = $data['modifier'];

        $notification->save();

        return $notification->id;

    }

    //更新推播訊錫
    public function updateMessage($data){

        $notification = Notification::find($data['id']);

        if($notification){

            $notification->modifier = $data['modifier'];

            if(array_key_exists('delete',$data) && $data['delete'] == '1' ){
                $notification->delete();
                return $notification->id;
            }

            $notification->title = $data['title'];
            $notification->body = $data['body'];
            if(array_key_exists('type',$data)){
                $notification->type = $data['type'];
            }else{
                $notification->type = 0;
            }

            $notification->sent = 0;
            $notification->url = $data['url'];
            $notification->platform = $data['platform'];
            $notification->time = $data['sendtime'];
            $notification->status = $data['status'];

            $notification->save();


            return $notification->id;

        }


    }

    //所有推播訊息
    public function allMessage(){

        $notifications = new Notification();

        return $notifications->all();

    }

    //查詢推播訊息
    public function queryMessage($id){

        $notification = new Notification();

        return $notification->find($id);
    }

}