<?php


namespace App\Http\Controllers\Api\V1;




use App\Services\ImageService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class GiftController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $couponService;
    protected $memberCouponService;
    protected $imageService;
    
    
    public function __construct(ImageService $imageService)
    {
        
        
        $this->imageService = $imageService;
    }
    
    /**
     *
     * 根據優惠卷類別與使用優惠卷單位(如餐車商家)之ID取得優惠卷列表
     *
     * @param Request $request
     *
     * @return void
     */
    public function list(Request $request) {
    
    
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
        $result = [
        
            'id'       => $id,
            'Name'     => '大碗公餐車',
            'title'    => '日本和牛丼飯 一份',
            'duration' => '2019-1-31',
            'content'  => '使用說明使用說明使用說明使用說明使用說明',
            'status'   => 0,
            'photo'    => "https://devbackend.citypass.tw/storage/diningCar/1/e1fff874c96b11a17438fa68341c1270_b.png",
    
        ];
        return $this->success($result);
        
    }
    
    
}


