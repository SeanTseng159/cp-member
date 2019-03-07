<?php

namespace App\Http\Controllers\Api\V1;


use App\Exceptions\ErrorCode;
use App\Result\MemberGiftItemResult;
use App\Services\ImageService;
use App\Services\Ticket\GiftService;
use App\Services\Ticket\MemberGiftItemService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class MemberGiftController extends RestLaravelController
{
    
    protected $lang = 'zh-TW';
    protected $memberGiftItemService;
    protected $imageService;
    
    
    public function __construct(MemberGiftItemService $memberGiftItemService,
                                ImageService $imageService)
    {
        
        $this->memberGiftItemService = $memberGiftItemService;
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
        try
        {
            $memberId = $request->memberId;
            $type = Input::get('type', 'current');
            $client = Input::get('client', null);
            $clientId = intval(Input::get('uid', null));
    
            if (!$memberId || !$type)
            {
                throw new \Exception('E0007');
            }
            
            
            
            //current 未使用 1 used 已使用 2
            if ($type == 'current')
            {
                $type = 1;
            }
            if ($type == 'used')
            {
                $type = 2;
            }
            
            //取得使用者的禮物清單
            $result = $this->memberGiftItemService->list($type, $memberId, $client, $clientId);
            
            $result = (new MemberGiftItemResult())->list($result,$type);
            
            
            return $this->success($result);
            
        }
        catch (\Exception $e)
        {
            if ($e->getMessage())
            {
                return $this->failureCode($e->getMessage());
            }
            return $this->failureCode('E0007');
        }
        
        
    }
    
    
    /**
     * 我的禮物列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request,
                         $id)
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
    public function getQrcode(Request $request,
                              $id)
    {
        $result = new \stdClass();
        $result->code = 'Um8eoj#WXP6Cy$Y2V*Bh';
        
        return $this->success($result);
        
    }
    
    
}
