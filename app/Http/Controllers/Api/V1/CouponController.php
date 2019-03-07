<?php


namespace App\Http\Controllers\Api\V1;



use App\Helpers\ImageHelper;
use App\Parameter\Ticket\CouponParameter;
use App\Result\Ticket\CouponResult;
use App\Services\Ticket\CouponService;
use App\Services\ImageService;
use App\Services\Ticket\MemberCouponService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class CouponController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $couponService;
    protected $memberCouponService;
    protected $imageService;
    
    
    public function __construct(CouponService $service, MemberCouponService $memberCouponService,ImageService $imageService)
    {
        $this->couponService = $service;
        $this->memberCouponService = $memberCouponService;
        $this->imageService = $imageService;
    }
    
    /**
     *
     * 根據優惠卷類別與使用優惠卷單位(如餐車商家)之ID取得優惠卷列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request) {
        
        try{
            
            $params = new CouponParameter($request);
            
            
            //coupon列表
            $coupons = $this->couponService->list($params);
            
            //使用者的coupon列表
            $userCoupons = $this->memberCouponService->list($params->memberId,$couponId = null);
            
            
            $result = (new CouponResult())->list($coupons,$userCoupons);
            
        
            return $this->success($result);
        
        }
        catch (\Exception $e){
            
            $errCode = $e->getMessage();
            if ($errCode){
                return $this->failureCode($errCode);
            }
        
            return $this->failureCode('E0007');
        }
    }
    
    
    /**
     * 根據id取得優惠卷明細
     *
     * @param Request $request
     * @param         $id       優惠卷id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    
    public function detail(Request $request,$id){
        try{
        
           
            //coupon詳細資料
            $couponDetail = $this->couponService->find($id);
            
            $params = new CouponParameter($request);
            //使用者的coupon資訊
            $userCoupons = $this->memberCouponService->list($params->memberId,$id)->first();
            
            //圖片資訊
            $images = ImageHelper::getImageUrl($couponDetail->model_type,$couponDetail->model_spec_id,1);
            
            $result = (new CouponResult())->detail($couponDetail,$userCoupons,$images);
        
            return $this->success($result);
        
        }
        catch (\Exception $e){
        
            $errCode = $e->getMessage();
            if ($errCode){
                return $this->failureCode($errCode);
            }
        
            return $this->failureCode('E0007');
        }
        
    }
    
    
}


