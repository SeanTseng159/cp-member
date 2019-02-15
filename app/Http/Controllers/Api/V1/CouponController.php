<?php


namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class CouponController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $service;
    protected $couponService;
    
    public function __construct() {
    
    }
    
    /** 根據優惠卷類別與使用優惠卷單位(如餐車商家)之ID取得優惠卷列表
     * @param $modelType
     * @param $modelSpecID
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list($modelType,$modelSpecID) {
    
        $result = [
            [
                "id"       => $modelSpecID,
                "Name"     => '大碗公餐車',
                "title"    => '加入會員送好禮',
                "content"  => '加入會員成功贈送紅茶冰一杯',
                "duration" => '2019-1-1～2019-12-31',
                "favorite" => false,
                "used"     => false,
            ],
            [
                "id"       => $modelSpecID,
                "Name"     => '大碗公餐車',
                "title"    => '買10碗送1碗',
                "content"  => '買10碗送1碗會員購買十碗排骨飯，加碼再送一碗！(可寄餐)',
                "duration" => '2019-1-1～2019-12-31',
                "favorite" => true,
                "used"     => true,
            ],
    
        ];
        
        return $this->success($result);
        
    }
    
    /**
     * 根據id取得優惠卷明細
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail($id){
        $result = [
            'photo'    => 'https://devbackend.citypass.tw/storage/menu/1/53e1b503077f1a89442ed331b6678f4f_b.png',
            "title"    => '加入會員送好禮',
            "duration" => '2019-1-1～2019-12-31',
            "content"  => '加入會員成功贈送紅茶冰一杯',
            "desc"     => '1. 限新註冊會員使用。<br>2. 不可與其他優惠合併使用。<br>3. 此電子優惠券為贈品，不可兌換現金或找零，亦不另開發票。<br>4. 請於點餐時向服務人員出示此電子優惠券兌換。<br>5. 本公司保有修改內容之權力。',
            "favorite" => true,
            "used"     => false,
        ];
        
        return $this->success($result);
        
    }
    
    
}


