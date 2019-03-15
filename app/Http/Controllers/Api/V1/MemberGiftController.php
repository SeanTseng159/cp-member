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
        
        //取得使用者的禮物清單
        $result = $this->memberGiftItemService->findByUserGiftId($memberId, $id);
        
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
     * @param         $id
     *
     * @return string
     */
    public function getQrcode(Request $request, $id)
    {
        try
        {
            $memberId = $request->memberId;
            
            //90秒
            $duration = Carbon::now()->addSeconds($this::DelayVerifySecond)->timestamp;
            $code = $this->qrCodePrefix.base64_encode("$memberId.$id.$duration");
            $result = new \stdClass();
            $result->code = $code;
            
            return $this->success($result);
            
        }
        catch (\Exception $e)
        {
            return $this->failureCode('E0007');
            
        }
        
        
    }
    
    /**
     *
     * 格式memberID.giftID.截止時間(timestamp)
     *
     * @param Request $request
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function useQrcode(Request $request)
    {
        
        try
        {
            $code = $request->gift_code;
            
            $info = base64_decode(str_replace($this->qrCodePrefix, '', $code));
            $data = explode(".", $info);
            $memberId = $data[0];
            $memberGiftId = $data[1];
            $duration = Carbon::createFromTimestamp(intval($data[2]));
            
            if ($duration->lt(Carbon::now()))
            {
                throw new \Exception('E0074');
            }
            
            $result = $this->memberGiftItemService->update($memberId,$memberGiftId);
            if(!$result)
            {
                throw new \Exception('E0075');
            }
            return $this->success();
            
            
        }
        catch (\Exception $e)
        {
            $errCode = $e->getMessage();
            if ($errCode)
            {
                return $this->failureCode($errCode);
            }
            
            return $this->failureCode('E0007');
            
        }
        
        
    }
    
    
}
