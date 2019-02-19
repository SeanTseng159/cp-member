<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class MemberCouponController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;
    protected $memberCouponService;
    
    
    /**
     * coupon 新增收藏
     *
     * @param $couponid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFavorite(Request $request){
        return $this->success(null);
        
        
    }
    
    /**
     * coupon 移除收藏
     *
     * @param $couponid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFavorite(Request $request){
        $result = [];
    
        return $this->success($result);
    
    }
    
    /**
     * 取得我的coupon可使用清單
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(){
        //type=current/used/expired

        $result = [
            [
                "id"       => 1,
                "Name"     => '大碗公餐車',
                "title"    => '加入會員送好禮',
                "content"  => '加入會員成功贈送紅茶冰一杯',
                "duration" => '2019-1-1～2019-12-31',
            ],
            [
                "id"       => 2,
                "Name"     => '大碗公餐車',
                "title"    => '買10碗送1碗',
                "content"  => '買10碗送1碗會員購買十碗排骨飯，加碼再送一碗！(可寄餐)',
                "duration" => '2019-1-1～2019-12-31',
            ],
        ];
    
        return $this->success($result);
    }
    

}
