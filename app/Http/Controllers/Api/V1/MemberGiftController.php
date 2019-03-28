<?php

namespace App\Http\Controllers\Api\V1;


use App\Result\MemberGiftItemResult;
use App\Services\ImageService;
use App\Services\Ticket\MemberGiftItemService;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class MemberGiftController extends RestLaravelController
{
    const DelayVerifySecond = 90 ;
    protected $lang = 'zh-TW';
    protected $memberGiftItemService;
    protected $imageService;
    protected $qrCodePrefix = 'gift_';
    
    
    public function __construct(MemberGiftItemService $memberGiftItemService, ImageService $imageService)
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
            
            $result = (new MemberGiftItemResult())->list($result, $type);
            
            
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
     * 我的禮物明細
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $memberId = $request->memberId;
        

        $result = $this->memberGiftItemService->findByGiftId($memberId, $id);
        
        if($result)
        {
            $result = (new MemberGiftItemResult())->show($result);
        }
        
        
        return $this->success($result);
    }


    /**
     * 格式: 編碼前 memberID.memberGiftItemID.$截止時間(timestamp)
     * ex.151.1.1551927111
     *
     * @param Request $request
     * @param $giftId
     * @return string
     */
    public function getQrcode(Request $request, $giftId)
    {
        try
        {
            $memberId = $request->memberId;
            
            //90秒
            $duration = Carbon::now()->addSeconds($this::DelayVerifySecond)->timestamp;
            $code = $this->qrCodePrefix.base64_encode("$memberId.$giftId.$duration");
            $result = new \stdClass();
            $result->code = $code;
            
            return $this->success($result);
            
        }
        catch (\Exception $e)
        {
            return $this->failureCode('E0007');
            
        }
        
    }
    

    
    
}
