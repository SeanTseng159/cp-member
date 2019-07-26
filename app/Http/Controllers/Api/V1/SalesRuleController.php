<?php

namespace App\Http\Controllers\Api\V1;

use App\Parameter\CheckoutParameter;
use App\Services\CartService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\Ticket\SalesRuleService;
use App\Traits\MemberHelper;

class SalesRuleController extends RestLaravelController
{
    use MemberHelper;

    private $service;

    public function __construct(SalesRuleService $service, CartService $cartService)
    {
        $this->cartService = $cartService;
        $this->service = $service;
    }

    /**
     * 使用折扣優惠
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCoupon(Request $request)
    {
        try {
            // 以code取得對應優惠碼
            $discount = $this->service->getEnableDiscountByCode($request->code);

            // 取出現有購物車
            $cart = $this->cartService->find('buyNow', $this->getMemberId());
            $cart = unserialize($cart);

            // 檢查是否可使用優惠碼
            $check_result = $this->service->checkCodeDiscount($discount,$this->getMemberId());

            //測試資料
            // $spec['id'] = 377;
            // $spec['name'] = "test_spec";
            // $type['id'] = 430;
            // $type['name'] = "test_spec";
            // $additional['spec'] =  $spec;
            // $additional['type'] =  $type;
            // $additional['usageTime'] =  '';
            // $additional['remark'] =  '';
            // $items[0]['id'] = 289;
            // $items[0]['name'] = 'test_name';
            // $items[0]['quantity'] = 1;
            // $items[0]['price'] = 4;
            // $items[0]['imageUrl'] = "https://devbackend.citypass.tw/storage/prod/141/07159caf07b851883afa7b0f04ba5348_s.jpg";
            // $items[0]['additional'] = $additional;
            // $items[0]['purchase'] = [];
            // $items[0]['retailPrice'] = 5;
            // $cart = new \stdClass();
            // $cart->type = 'buyNow';
            // $cart->totalQuantity = 1;
            // $cart->totalAmount = 1114;
            // $cart->discountAmount = 0;
            // $cart->discountTotalAmount = 0;
            // $cart->shippingFee = 3;
            // $cart->payAmount = 7;
            // $cart->canCheckout = true;
            // $cart->hasPhysical = true;
            // $cart->promotion = null;
            // $cart->items = $items;
            
            if ($check_result) {
                // 加入優惠碼至購物車
                $data = $this->cartService->setAddDiscountCode($cart, $discount, $this->getMemberId());
                if(!$data) return $this->failureCode('E0503');
            }

            return $this->success($data);
        } catch (\Exception $e) {
            \Log::error(__METHOD__, ['message' => $e->getMessage()]);
            return $this->failureCode('E0101');
        }
    }

    /**
     * 取消折扣優惠
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCoupon(Request $request)
    {
        try {
            // 取出現有購物車
            $cart = $this->cartService->find('buyNow', $this->getMemberId());
            $cart = unserialize($cart);

            //刪除
            unset($cart->DiscountCode);

            $cart->discountAmount = 0;
            $cart->discountTotalAmount = 0;
            $cart->payAmount = $data->totalAmount;
            $this->cartService->add('buyNow',$this->getMemberId(),$cart);

            return $this->success();
        } catch (\Exception $e) {
            \Log::error(__METHOD__, ['message' => $e->getMessage()]);
            return $this->failureCode('E0102');
        }
    }


}
// 購物車結構範例
//            "type": "buyNow",
//        "items": [
//            {
//                "id": 289,
//                "name": "test_name",
//                "quantity": 1,
//                "price": 4,
//                "imageUrl": "https://devbackend.citypass.tw/storage/prod/141/07159caf07b851883afa7b0f04ba5348_s.jpg",
//                "additional": {
//                    "spec": {
//                        "id": 377,
//                        "name": "test_spec"
//                    },
//                    "type": {
//                        "id": 430,
//                        "name": "test_spec"
//                    },
//                    "usageTime": "",
//                    "remark": ""
//                },
//                "purchase": [],
//                "retailPrice": 5
//            }
//        ],
//        "totalQuantity": 1,
//        "totalAmount": 4,全部金額
//        "discountAmount": 0,折扣價格(四捨五入)
//        "discountTotalAmount": 4,折抵後小記
//        "shippingFee": 3,運費
//        "payAmount": 7,同discountTotalAmount
//        "canCheckout": true,
//        "hasPhysical": true,
//        "promotion": null