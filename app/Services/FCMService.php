<?php
/**
 * Created by PhpStorm.
 * User: Danny
 * Date: 2019/7/25
 * Time: 下午 01:51
 */

namespace App\Services;


use App\Enums\DeviceType;
use App\Models\NotificationMobile;
use App\Repositories\NotificationRepository;
use Carbon\Carbon;
use Log;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use LaravelFCM\Message\Topics;
use App\Parameter\DiningCarParameter;
use App\Repositories\Ticket\MemberNoticRepository;


class FCMService
{
    protected $repository;
    protected $memberNoticRepository;

    public function __construct(NotificationRepository $repository,
                                MemberNoticRepository $memberNoticRepository)
    {
        $this->repository = $repository;
        $this->memberNoticRepository = $memberNoticRepository;
    }

    public function notifyOne($memberId, $notificationId, $isTest)
    {
        $notification = $this->find($notificationId);

        //已發送過且非測試
        if ($notification->sent && !$isTest) {
            return false;
        }

        $title = $notification->title;
        $body = $notification->body;
        $arrayData = $this->toFcmData($notification);

        $tokens = NotificationMobile::where('member_id', $memberId)->get();
        $tokens = $tokens->pluck('mobile_token')->toArray();
//      測試用token
//        $tokens = [
//            'c58l4_Fp9RA:APA91bE6bk3PHMO4QQWtpWbbHZ9-LHGAPjTWTRxDyRJsuHSCeMrsfXxDBxc8CIm3RhAikP5k86GYNDuZxa5wQNTmkGkLetue_ySwqVFerUkswFSHTfvGHFwIjhQjrhgHBk-6exKSs_Tk',
//            'dbaZwcVUC4A:APA91bHsdeLO9D-CN8bYIRSWq4_HpSbbZ4rTBjun0BG4c8kxzG1cyr19MNZcSkeGgwEy280VKKf-NRrtDLCFjb_OUf4BlZKD4FtqqwrYYY3ZlYdmm6agSPr3BGYvOVIAbYLETrcBZ-GP'
//        ];
        $this->toDevice($tokens, $title, $body, $arrayData);

        if (!$isTest) {
            $notification->sent = 1;
            $notification->time = Carbon::now();
            $notification->save();
        }

        return $arrayData;
    }

    public function notifyMultiple($memberIds, $title, $body, $data)
    {
        $webtokens = NotificationMobile::whereIn('member_id', $memberIds)->where('platform','web')->get();
        $webtokens = $webtokens->pluck('mobile_token')->toArray();
        $isweb = true;
        $this->toDevice($webtokens, $title, $body, $data ,$isweb);
        //
        $tokens = NotificationMobile::whereIn('member_id', $memberIds)->where('platform','!=','web')->get();
        $tokens = $tokens->pluck('mobile_token')->toArray();
        $isweb = false;
        $this->toDevice($tokens, $title, $body, $data ,$isweb);

        return $data;
    }

    public function memberNotify($event,$memberIds,$data)
    {
        switch ($event) {
            case 'addMember':
                $title = '加入餐車會員';
                $body = '您已成功加入'.$data['diningCarName'];
                break;
            case 'addGift':
                $title = '獲得禮物通知';
                $body = '您獲得了'.$data['giftName'];
                break;
            case 'getPoint':
                $title = '訊息通知';
                $body = $data['point'] > 0 ?'您消費獲得了'.$data['point'].'點':'';
                if($data['addmemberCheck']){$body = '您已成功加入'.$data['diningCarName'].'，'.$body;}
                if($data['giftCheck']){$body = $body.'並獲得了'.$data['giftName'];}
                break;
            case 'inviteSuccess':
                $title = '邀請好友註冊成功';
                $body = '您的好友'.$data['name'].'成功加入CityPass都會通！恭喜您可以獲得'.$data['giftName'];
                break;
            case 'remindMemberGiftAndCoupon':
                $title = '到期通知';
                $body = '您的 '.$data['name'].' 即將過期，請儘速使用！';
                break;
            case 'giftChange':
                $title = '點數兌換通知';
                $body = '您以'.$data['point'].'點兌換了'.$data['qty'].'份'.$data['name'];
                break;
            case 'diningCarMemberLevelUp':
                $title = '會員等級變更';
                $body = '您已升等為 '.$data['name'];
                break;

            default:
                # code...
                break;
        }
        //推播紀錄存放資料庫
        $params = (new DiningCarParameter)->noticInfo($data,$body);
        $params['member_id'] = $memberIds[0];
        $params['created_at'] = Carbon::now();
        $this->memberNoticRepository->addRecord($params);
        $this->notifyMultiple($memberIds, $title, $body, $data);
    }

    public function notify($notificationId)
    {
        try {

            $notification = $this->find($notificationId);

            //已發送過且非測試
            if (!$notification || $notification->sent) {
                return false;
            }

            $title = $notification->title;
            $body = $notification->body;
            $platform = $notification->platform;
            $arrayData = $this->toFcmData($notification);
            $this->pushNotification($title, $body, $arrayData, $platform);

            \DB::beginTransaction();
            $notification->sent = 1;
            $notification->time = Carbon::now();
            $notification->save();
            \DB::commit();
            return $arrayData;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            \DB::rollBack();
            return false;
        }
    }


    public function schedule()
    {
        try {
            $data = $this->repository->getDurationNotification(60)->get();
            foreach ($data as $item) {
                $id = $item->id;
                $isSuccess = $this->notify($id);
                if (!$isSuccess) {
                    Log::info('==== notification  fail :' . $id);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    private function toFcmData($data)
    {
        $obj = new \stdClass();
        $obj->url = $data->link;
        $obj->prodType = $data->app_type;
        $obj->prodId = $data->app_type_id;
        return (array)$obj;

    }

    private function toDevice($tokens, $title, $body, $data, $isweb = false)
    {
        if (empty($tokens)) {
            return;
        }
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)
            ->setSound('default');
        if($isweb)
        {
            $notificationBuilder->setClickAction($data['url']);
            $notificationBuilder->setIcon('https://scontent.fkhh1-1.fna.fbcdn.net/v/t1.0-9/60443313_2371437916474915_6204651090290409472_n.jpg?_nc_cat=102&_nc_oc=AQn8HkrJaV57l3fCG1y3rpFKiWu_Lq8Jg8df2bmx_iJV4itYAWOhPDOiQkgAmi-o3QE&_nc_ht=scontent.fkhh1-1.fna&oh=fbac926b0cba076eee39010173c3784f&oe=5DC24FDB');
        }

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($data);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();
        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

        //return Array - you must remove all this tokens in your database
        //$deleteTokes = $downstreamResponse->tokensToDelete();

        //return Array (key : oldToken, value : new token - you must change the token in your database )
        //$downstreamResponse->tokensToModify();

        //return Array - you should try to resend the message to the tokens in the array
        //$downstreamResponse->tokensToRetry();

        // return Array (key:token, value:errror) - in production you should remove from your database the tokens

    }


    private function pushNotification($title, $body, $data, $platform)
    {
        switch ($platform) {
            case DeviceType::All:
//                Log::info('$this->toAll');
                $this->toAll($title, $body, $data);
                break;
            case DeviceType::Android:
//                Log::info('$this->toAndroid');
                $this->toAndroid($title, $body, $data);
                break;
            case DeviceType::IOs:
//                Log::info('$this->IOs');
                $this->toIos($title, $body, $data);
                break;
            case DeviceType::Web:
//                Log::info('$this->toWeb');
                $this->toWeb($title, $body, $data);
                break;
            default:
                Log::info("platform error:{$platform}");
                break;
        }


    }

    private function toAll($title, $body, $data)
    {
        $this->withTopic($title, $body, 'allDevice', $data);
    }

    private function toIos($title, $body, $data)
    {
        $this->withTopic($title, $body, 'iOS', $data);
    }

    //todo 確定topic name = android
    private function toAndroid($title, $body, $data)
    {
        $this->withTopic($title, $body, 'android', $data);
    }

    //todo 確定topic name = web
    private function toWeb($title, $body, $data)
    {
        $this->withTopic($title, $body, 'web', $data);
    }

    private function withTopic($title, $body, $topicName, $data)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);
        $option = $optionBuilder->build();

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($body)
            ->setSound('default');

        if($topicName==='web')
        {
            $frond_domain = env('FRONTEND_DOMAIN');
            //
        switch ($data['prodType']) {
            case '0':
                $notificationBuilder->setClickAction($data['url']);
                break;
            case '1':
                # code...
                break;
            case '2':
                $url =  $frond_domain. '/product/c/' . $data['prodId'];
                $notificationBuilder->setClickAction($url);
                break;
            case '3':
                $url =  $frond_domain. '/product/p/' . $data['prodId'];
                $notificationBuilder->setClickAction($url);
                break;
            case '4':
                $url =  $frond_domain. '/promotion/' . $data['prodId'];
                $notificationBuilder->setClickAction($url);
                break;
            case '5':
                if($data['prodId'] === 0)
                {
                    $url =  $frond_domain. '/diningCar';
                    var_dump($url);
                    $notificationBuilder->setClickAction($url);
                }else
                {
                    $url =  $frond_domain.  '/diningCar/detail/' . $data['prodId'];
                    $notificationBuilder->setClickAction($url);
                }
                break;
            }
        //
        }

        $notification = $notificationBuilder->build();
        $topic = new Topics();
        $topic->topic($topicName);

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($data);
        $fcmData = $dataBuilder->build();

        $topicResponse = FCM::sendToTopic($topic, $option, $notification, $fcmData);

        $topicResponse->isSuccess();
        $topicResponse->shouldRetry();
        $topicResponse->error();

    }

}