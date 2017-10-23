<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/17
 * Time: 下午 05:30
 */

namespace Ksd\Mediation\Services;

use Ksd\Mediation\Repositories\NotificationRepository;

use Mockery\CountValidator\Exception;
use Sly\NotificationPusher\PushManager;
use Sly\NotificationPusher\Adapter\Apns as ApnsAdapter;
use Sly\NotificationPusher\Adapter\Gcm as GcmAdapter;
use Sly\NotificationPusher\Collection\DeviceCollection;
use Sly\NotificationPusher\Model\Device;
use Sly\NotificationPusher\Model\Message;
use Sly\NotificationPusher\Model\Push;

class NotificationService
{
    private $repository;
    private $env;           //環境變數

    //環境參數選項
    //0: 開發中, 1:產品
    public $env_opt = array(
        0   => PushManager::ENVIRONMENT_DEV,    //測試環境
        1   => PushManager::ENVIRONMENT_PROD,   //正式環境
    );

    //iOS 平台參數
    public $iOS_platform = array(
        PushManager::ENVIRONMENT_DEV        =>  'iOS-Dev',
        PushManager::ENVIRONMENT_PROD       =>  'iOS',
    );

    //iOS 憑證
    public $iOS_certificate = array(
        //PushManager::ENVIRONMENT_DEV        =>  'C:\Users\ching\Desktop\VisualAMPv7\www\City-pass-member\src\Ksd\Mediation\Services\CityPass_APS_Development.pem',
        PushManager::ENVIRONMENT_DEV        =>  __DIR__.'/CityPass_APS_Development.pem',
        PushManager::ENVIRONMENT_PROD       =>  __DIR__.'/CityPass_APS.pem',
    );

    //Android 平台參數
    public $android_platform = array(
        PushManager::ENVIRONMENT_DEV        =>  'Android',
        PushManager::ENVIRONMENT_PROD       =>  'Android',
    );

    //Android api key
    public $android_apiKey = array(
        PushManager::ENVIRONMENT_DEV        =>  'YourApiKey',
        PushManager::ENVIRONMENT_PROD       =>  'YourApiKey',
    );

    public function __construct()
    {
        $this->repository = new NotificationRepository();

        $this->env = PushManager::ENVIRONMENT_DEV;    //測試環境
        //$this->env = PushManager::ENVIRONMENT_PROD;   //正式環境
    }

    //註冊推播金鑰
    public function register($data){

        return $this->repository->register($data);

    }

    //發送推播訊息
    public function send($data){
        try{

            // Then, create the push skel.

            $message = new Message($data['body'], array(
                'badge' => 1,
                'sound' => 'default',

                'actionLocKey' => '',
                'locKey' => $data['title'],
                'locArgs' => array(

                ),
                'launchImage' => '',

                'custom' => array(
                    'type'  => $data['type'],
                    'url'   => $data['url'],
                )
            ));


            //iOS
            if($data['platform']==='0' || $data['platform']==='1'){

                //取出已註冊iOS Token
                //$registedDevices = $this->repository->devicesByPlatform('iOS');
                //$registedDevices = $this->repository->devicesByPlatform('iOS-Dev');
                $registedDevices = $this->repository->devicesByPlatform($this->iOS_platform[$this->env]);


                while($registedDevices->count() > 0){

                    //送出訊息
                    $deviceTokens = array();

                    foreach($registedDevices as $key=>$registedDevice){
                        try{
                            array_push($deviceTokens,new Device($registedDevice->mobile_token));
                            // Set the device(s) to push the notification to.
                        }catch(\Exception $e){

                        }
                    }
                    $devices = new DeviceCollection(
                        $deviceTokens
                    );

                    //pushManager
                    $pushManager = new PushManager($this->env);

                    // Then declare an adapter.
                    $apnsAdapter = new ApnsAdapter(array(
                        'certificate' => $this->iOS_certificate[$this->env],
                    ));

                    $push = new Push($apnsAdapter, $devices, $message);
                    $pushManager->add($push);
                    $pushManager->push();

                    $push_responses = $push->getResponses();

                    //移除成功傳送token
                    foreach($push_responses as $token => $response) {
                        if(!is_null($response['id'])){
                            break;
                        }else{
                            foreach($registedDevices as $key=>$registedDevice){
                                if($token == $registedDevice->mobile_token){
                                    unset($registedDevices[$key]);
                                }
                            }
                        }

                    }

                    //移除失敗token
                    foreach($push_responses as $token => $response) {
                        if(!is_null($response['id'])){
                            foreach($registedDevices as $key=>$registedDevice){
                                if($token == $registedDevice->mobile_token){
                                    unset($registedDevices[$key]);
                                    $this->repository->deleteByToken($token);
                                }
                            }
                        }
                    }

                }


            }

            //Android
            if($data['platform']=='0' || $data['platform']=='2'){

                //取出已註冊Android Token
                $registedDevices = $this->repository->devicesByPlatform($this->android_platform[$this->env]);

                while($registedDevices->count() > 0){

                    //送出訊息
                    $deviceTokens = array();

                    foreach($registedDevices as $key=>$registedDevice){
                        try{
                            array_push($deviceTokens,new Device($registedDevice->mobile_token));
                            // Set the device(s) to push the notification to.
                        }catch(\Exception $e){

                        }
                    }
                    $devices = new DeviceCollection(
                        $deviceTokens
                    );


                    //pushManager
                    $pushManager = new PushManager($this->env);

                    // Then declare an adapter.
                    $gcmAdapter = new GcmAdapter(array(
                        'apiKey' => $this->android_apiKey[$this->env],
                    ));

                    // Finally, create and add the push to the manager, and push it!
                    $push = new Push($gcmAdapter, $devices, $message);
                    $pushManager->add($push);
                    $pushManager->push(); // Returns a collection of notified devices

                    $push_responses = $push->getResponses();

                    //移除成功傳送token
                    foreach($push_responses as $token => $response) {
                        if(!is_null($response['id'])){
                            break;
                        }else{
                            foreach($registedDevices as $key=>$registedDevice){
                                if($token == $registedDevice->mobile_token){
                                    unset($registedDevices[$key]);
                                }
                            }
                        }

                    }

                    //移除失敗token
                    foreach($push_responses as $token => $response) {
                        if(!is_null($response['id'])){
                            foreach($registedDevices as $key=>$registedDevice){
                                if($token == $registedDevice->mobile_token){
                                    unset($registedDevices[$key]);
                                    $this->repository->deleteByToken($token);
                                }
                            }
                        }
                    }

                }



            }

            //測試指定用戶
            if($data['platform']=='3'){


                $registedDevices = $this->repository->devicesByMember($data['memberId']);


                foreach($registedDevices as $key=>$registedDevice){

                    try{
                        //送出訊息
                        $devices = new DeviceCollection(
                            array(
                                new Device($registedDevice->mobile_token)
                            )
                        );

                        //pushManager
                        $pushManager = new PushManager($this->env);

                        switch($registedDevice->platform){
                            case $this->iOS_platform[$this->env]:
                                $adapter = new ApnsAdapter(array(
                                    'certificate' => $this->iOS_certificate[$this->env],
                                ));
                                break;
                            case $this->android_platform[$this->env]:
                                $adapter = new GcmAdapter(array(
                                    'apiKey' => $this->android_apiKey[$this->env],
                                ));
                                break;
                            default:
                                break;
                        }

                        // Finally, create and add the push to the manager, and push it!
                        $push = new Push($adapter, $devices, $message);
                        $pushManager->add($push);
                        $pushManager->push(); // Returns a collection of notified devices

                        $push_responses = $push->getResponses();

                        //移除失敗token
                        foreach($push_responses as $token => $response) {
                            if(!is_null($response['id'])){
                                $this->repository->deleteByTokenPlatform($token, $registedDevice->platform );
                            }
                        }
                    }catch(\Exception $e){

                    }

                }

            }


            return true;

        }catch(\Exception $e){
            var_dump($e);
        }


    }
}