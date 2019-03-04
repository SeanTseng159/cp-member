<?php

namespace App\Http\Controllers\Api\V1;


use App\Services\ImageService;
use App\Services\Ticket\GiftService;
use App\Services\Ticket\MemberGiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class MemberGiftController extends RestLaravelController
{
    
    protected $lang = 'zh-TW';
    protected $giftService;
    protected $memberGiftService;
    protected $imageService;
    
    
    public function __construct(GiftService $service,
                                MemberGiftService $memberCouponService,
                                ImageService $imageService)
    {
        $this->giftService = $service;
        $this->memberGiftService = $memberCouponService;
        $this->imageService = $imageService;
    }
    
    /**
     * 我的禮物列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $memberId = $request->memberId;
        
        $type = Input::get('type', 'current');
        $client = Input::get('client', null);
        $clientId = Input::get('uid', null);
        
        
        //current 未使用 1
        //used    已使用 2
        $result = '';
        
        
        if ($type == 'current')
        {
            $this->memberGiftService->list($type,$memberId,$client,$clientId);
            
            
        }
        else if ($type == 'used')
        {
            $result = [
                [
                    'id'       => 1,
                    'Name'     => '大碗公餐車',
                    'title'    => '日本和牛丼飯 一份',
                    'duration' => '2019-1-31',
                    'photo'    => "https://devbackend.citypass.tw/storage/diningCar/1/e1fff874c96b11a17438fa68341c1270_b.png",
                    'status'   => 1,
                ],
                [
                    'id'       => 2,
                    'Name'     => '咖啡店',
                    'title'    => '拿鐵咖啡一杯',
                    'duration' => '2019-1-31',
                    'photo'    => "https://devbackend.citypass.tw/storage/diningCar/1/e1fff874c96b11a17438fa68341c1270_b.png",
                    'status'   => 2,
                ],
            ];
        }
        
        $client = Input::get('client', null);
        $uid = Input::get('uid', null);
        if ($client && $uid)
        {
            $result = [
                [
                    'id'       => 1,
                    'Name'     => '大碗公餐車',
                    'title'    => '日本和牛丼飯 一份',
                    'duration' => '2019-1-31',
                    'photo'    => "https://devbackend.citypass.tw/storage/diningCar/1/e1fff874c96b11a17438fa68341c1270_b.png",
                    'status'   => 1,
                ]
            ];

        }
        
        return $this->success($result);
        
    }
    
    
    /**
     * 我的禮物列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request,$id)
    {
        $memberId = $request->memberId;
        
        $result
            = [
            'id'       => 1,
            'Name'     => '大碗公餐車',
            'title'    => '丼飯吃吃吃',
            'duration' => '2019-1-31',
            'photo'    => "https://devbackend.citypass.tw/storage/diningCar/1/e1fff874c96b11a17438fa68341c1270_b.png",
            'content'  => '日本和牛丼飯 一份 內用',
            'desc'     => '使用說明使用說明使用說明使用說明使用說明使用說明',
            'status'   => 0,
        ];
        
        return $this->success($result);
    }
    
    
    
    /**
     * @param Request $request
     * @param         $id
     *
     * @return string
     */
    public function getQrcode(Request $request,$id)
    {
        $result = new \stdClass();
        $result->code = 'Um8eoj#WXP6Cy$Y2V*Bh';
    
        return $this->success($result);
        
    }




    
    
}
