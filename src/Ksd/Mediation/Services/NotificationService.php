<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 05:30
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Repositories\NotificationRepository;

use Sly\NotificationPusher\PushManager;
use Sly\NotificationPusher\Adapter\Apns as ApnsAdapter;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;

class NotificationService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new NotificationRepository();
    }

    //註冊推播金鑰
    public function register($data){

        return $this->repository->register($data);

    }

    //發送推播訊息
    public function send($data){

        //$notimob = new NotificationMobile();

        //iOS
        if($data['platform']==='0' || $data['platform']==='1'){
            $devices = $this->repository->devicesByPlatform('iOS');

            var_dump($devices);
        }

        //Android
        if($data['platform']=='0' || $data['platform']=='2'){

        }

        //測試指定用戶
        if($data['platform']=='3'){

        }


        return true;

    }
}