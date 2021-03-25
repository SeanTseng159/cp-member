<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\SalesRule\CouponParameter;
use Ksd\Mediation\Services\SalesRuleService;
use Ksd\Mediation\Services\CartMoreService;
use App\Traits\MemberHelper;

class SalesRuleController extends RestLaravelController
{
    private $service;
    protected $cartMoreService;
    use MemberHelper;


    public function __construct(SalesRuleService $service, CartMoreService $cartMoreService)
    {
        $this->service = $service;
        $this->cartMoreService = $cartMoreService;
    }

    /**
     * 使用折扣優惠
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCoupon(Request $request)
    {
        $parameters = new CouponParameter();
        $parameters->laravelRequest($request);
        
        $salesRule = $this->service->addCoupon($parameters);


        $returnObj = new \stdClass();
        $returnObj->code = ($salesRule['statusCode'] == 201) ? '00000' : 'E00002';
        $returnObj->message = ($returnObj->code == '00000') ? 'success' : $salesRule['data'];

        return ($returnObj->code == '00000') ? $this->success($salesRule['data']) : $this->failure($returnObj->code, $returnObj->message);
    }

    /**
     * 取消折扣優惠
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCoupon(Request $request)
    {

        $parameters = new CouponParameter();
        
        $parameters->laravelRequest($request);

        $salesRule = $this->service->deleteCoupon($parameters);


        $returnObj = new \stdClass();
        $returnObj->code = ($salesRule['statusCode'] == 203) ? '00000' : 'E00002';
        $returnObj->message = ($returnObj->code == '00000') ? 'success' : $salesRule['data'];


        return ($returnObj->code == '00000') ? $this->success($salesRule['data']) : $this->failure($returnObj->code, $returnObj->message);

    }
    
    /**
     * 使用"店家新增之優惠券"的折扣優惠
     * 購物車結帳頁面，點選使用折價券並確認使用時，前端call此api，回應該優惠券資料
     * 此api僅針對"店家新增之優惠券"進行使用，若為"站方新增的優惠券"，則是使用addCoupon function
     * 此兩function功能類似都是檢查優惠券是否可用並抓取資料，但兩者使用不同資料表，開發時間也不同
     * 故店家新增之優惠券的使用api獨立拉出來寫，此部分的新增功能寫在vendor的CouponOnlineController內
     * Note:兩功能的input及output相同，方便前端作業
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCouponOnline(Request $request)
    {
        //將送進來的request資料，賦值到需要的欄位 EX => data:{source": "ct_pass_physical","code": "percentOneDollor2","cartNumber": "1"}
        $parameters = new CouponParameter();
        $parameters -> laravelRequest($request);

        $memberID = $this->getMemberId();//抓user_id,以便後續檢查時使用

        //傳值進入service，拿取此次訂單的總價、優惠券折價價格、訂單折扣後價格...等資料
        $salesRule = $this->service->addCouponOnline($parameters,$memberID);//return json 可用/不可用

        //透過CI專案去取得購物車的資料，來得知購物車內有多少商品與價值
        $cartItems = $this->cartMoreService->mine(['cartNumber' => $parameters->cartNumber]);
        //注意! 此$cartItems是由CI專案傳回來的資料，裡面也會含一份DiscountCode相關資料，但那並不準，請勿拿來用，優惠資料請拿上方$salesRule得到的資料
        
        //$test = $this->cartMoreService->info(['cartNumber' => $parameters->cartNumber]);

        //DEBUG
        //return $this->success($parameters);
        //return $this->success($cartItems);
        //return $this->success($salesRule[0]);
        //return $this->success($test);

        $return_data = new \stdClass();
        $return_data->totalAmount = $cartItems[0]->totalAmount;//商品金額
        $return_data->totalQuantity = $cartItems[0]->itemTotal;//商品總數量
        $return_data->DiscountCode['id'] = $salesRule[0]->id;//優惠券id
        $return_data->DiscountCode['code'] = $parameters->code;//優惠券代碼
        $return_data->DiscountCode['name'] = $salesRule[0]->name;//優惠券名稱
        $return_data->DiscountCode['method'] = $salesRule[0]->online_code_type;//優惠方式 1折數(*) 2折扣(-)
        $return_data->DiscountCode['price'] = $salesRule[0]->price;

        //===計算折扣總金額===
        if($return_data->DiscountCode['method'] == 1){
            $return_data->DiscountCode['amount'] = $return_data->totalAmount - ($return_data->totalAmount*$return_data->DiscountCode['price']/100); //若折抵為折數，折扣總金額 = 原價-打折後價格
        }
        else if($return_data->DiscountCode['method'] == 2){
            $return_data->DiscountCode['amount'] = $return_data->DiscountCode['price']; //若為折價，折扣總金額就是折價
        }
        if($salesRule[0]->online_code_off_max < $return_data->DiscountCode['amount']){//如果算出折價金額比最高折抵還多，以最高折抵額為主
            $return_data->DiscountCode['amount'] = $salesRule[0]->online_code_off_max;
        }

        $return_data->DiscountCode['amount'] = round($return_data->DiscountCode['amount']);//如果打折金額
        //此部分請示過會計，四捨五入必須寫在"折價總金額"上，若折109.6元則折110元，折109.4元則折109元，折價金額就不要有小數點了。
        //===計算折扣總金額===

        $return_data->discountAmount = $return_data->DiscountCode['amount'];//折扣總金額(買一千打九折 折扣總金額=100)
        $return_data->discountTotalAmount = $return_data->totalAmount - $return_data->discountAmount; //商品最後金額 = 原價 - 折扣總金額
        $return_data->shipmentAmount = $cartItems[0]->shipmentAmount;//運費
        $return_data->shippingFee = $cartItems[0]->shipmentAmount;//運費(拿來加回總額用)
        $return_data->shipmentFree = $cartItems[0]->shipmentFree;//滿多少免運門檻
        $return_data->payAmount = $return_data->discountTotalAmount + $return_data->shippingFee;//付款總額 = 商品最後金額 + 運費
        return $this->success($return_data);


    }
}
