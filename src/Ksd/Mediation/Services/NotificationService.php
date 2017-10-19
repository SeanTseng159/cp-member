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
        try{
            //正式環境
            //$pushManager = new PushManager(PushManager::ENVIRONMENT_PROD);
            //測試環境
            $pushManager = new PushManager(PushManager::ENVIRONMENT_DEV);

            // Then, create the push skel.
            $message = new Message($data['title'], array(
                'badge' => 1,
                'sound' => 'example.aiff',

                'actionLocKey' => 'Action button title!',
                'locKey' => 'localized key',
                'locArgs' => array(
                    'localized args',
                    'localized args',
                    'localized args'
                ),
                'launchImage' => 'image.jpg',

                'custom' => array('custom data' => array(
                    'we' => 'want', 'send to app'
                ))
            ));

            //iOS
            if($data['platform']==='0' || $data['platform']==='1'){

                // Then declare an adapter.
                $apnsAdapter = new ApnsAdapter(array(
                    'certificate' => 'C:\Users\ching\Desktop\VisualAMPv7\www\City-pass-member\src\Ksd\Mediation\Services\CityPass_APS_Development.pem',
                ));

                //取出已註冊iOS Token
                //$registedDevices = $this->repository->devicesByPlatform('iOS');
                $registedDevices = $this->repository->devicesByPlatform('iOS-Dev');

                $deviceTokens = array();

                var_dump($registedDevices->count());

                foreach($registedDevices as $key=>$registedDevice){
                    var_dump($registedDevice->mobile_token);
                    array_push($deviceTokens,new Device($registedDevice->mobile_token));
                }

                // Set the device(s) to push the notification to.
                $devices = new DeviceCollection(
                    $deviceTokens
                );

                // Finally, create and add the push to the manager, and push it!
                $push = new Push($apnsAdapter, $devices, $message);
                $pushManager->add($push);
                $pushManager->push();

                foreach($push->getResponses() as $token => $response) {
                    // ...
                }
            }

            //Android
            if($data['platform']=='0' || $data['platform']=='2'){

                // Then declare an adapter.
                $gcmAdapter = new GcmAdapter(array(
                    'apiKey' => 'YourApiKey',
                ));

                $registedDevices = $this->repository->devicesByPlatform('Android');

                $deviceTokens = array();

                foreach($registedDevices as $key=>$registedDevice){
                    array_push($deviceTokens,new Device($registedDevice->mobile_token));
                }

                $devices = new DeviceCollection(
                    $deviceTokens
                );

                // Finally, create and add the push to the manager, and push it!
                $push = new Push($gcmAdapter, $devices, $message);
                $pushManager->add($push);
                $pushManager->push(); // Returns a collection of notified devices

                // each response will contain also
                // the data of the overall delivery
                foreach($push->getResponses() as $token => $response) {
                    // > $response
                    // Array
                    // (
                    //     [message_id] => fake_message_id
                    //     [multicast_id] => -1
                    //     [success] => 1
                    //     [failure] => 0
                    //     [canonical_ids] => 0
                    // )
                }

            }

            //測試指定用戶
            if($data['platform']=='3'){

                $registedDevices = $this->repository->devicesByMember($data['memberId']);



            }


            return true;

        }catch(Exception $e){
            var_dump($e);
        }


    }
}