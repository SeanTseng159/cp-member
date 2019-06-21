<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 06:01
 */

namespace Ksd\Mediation\Repositories;

use App\Enum\DevicePlatform;
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

    /**
     * 如果有memberID -> 刪除舊的資料app/web 並新增一筆新的資料
     * 如果沒有        -> insert一筆
     * @param $token
     * @param $platform
     * @param null $memberId
     * @return bool
     */
    public function register($token, $platform, $memberId = null)
    {


        if ($memberId) {
            if ($platform == DevicePlatform::iOS or $platform == DevicePlatform::Android) {

                NotificationMobile::where('member_id', $memberId)
                    ->whereIn('platform', [DevicePlatform::iOS, DevicePlatform::Android, DevicePlatform::iOS_DEV])
                    ->delete();

            } else if ($platform == DevicePlatform::web) {
                NotificationMobile::where('member_id', $memberId)
                    ->where('platform', DevicePlatform::web)
                    ->delete();
            }

            $newToken = new NotificationMobile();
            $newToken->mobile_token = $token;
            $newToken->platform = $platform;
            $newToken->member_id = $memberId;
            $newToken->save();
            return true;
        } else {
            $newToken = new NotificationMobile();
            $newToken->mobile_token = $token;
            $newToken->platform = $platform;
            $newToken->save();
            return true;
        }
        return false;
    }

    //取得平台裝置註冊金鑰
    public function devicesByPlatform($platform)
    {

        $notimob = new NotificationMobile();

        $devices = $notimob->where([
            ['platform', '=', $platform],
        ])
            ->get();

        return $devices;
    }


    //取得使用者註冊金鑰
    public function devicesByMember($member)
    {

        $notimob = new NotificationMobile();

        $devices = $notimob->where([
            ['member_id', '=', $member],
        ])
            ->get();

        return $devices;
    }

    //刪除token
    public function deleteByToken($token)
    {
        $notimob = new NotificationMobile();

        $notimob->where([
            ['mobile_token', '=', $token],
        ])
            ->delete();

    }

    //刪除toekn-platform
    public function deleteByTokenPlatform($token, $platform)
    {
        $notimob = new NotificationMobile();

        $notimob->where([
            ['mobile_token', '=', $token],
            ['platform', '=', $platform],
        ])
            ->delete();
    }


    //新增推播訊錫
    public function createMessage($data)
    {
        $notification = new Notification();

        $notification->title = $data['title'];
        $notification->body = $data['body'];
        if (array_key_exists('type', $data)) {
            $notification->type = $data['type'];
        } else {
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
    public function updateMessage($data)
    {

        $notification = Notification::find($data['id']);

        if ($notification) {

            $notification->modifier = $data['modifier'];

            if (array_key_exists('delete', $data) && $data['delete'] == '1') {
                $notification->delete();
                return $notification->id;
            }

            $notification->title = $data['title'];
            $notification->body = $data['body'];
            if (array_key_exists('type', $data)) {
                $notification->type = $data['type'];
            } else {
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
    public function allMessage($data)
    {

        $notifications = new Notification();

        $notis = null;

        if (array_key_exists('date', $data)) {
            $notis = $notifications->getAfterDate($data['date']);

        } else {
            $notis = $notifications->all();
        }

        return $notis;

    }

    //查詢推播訊息
    public function queryMessage($id)
    {

        $notification = new Notification();

        return $notification->find($id);
    }

}