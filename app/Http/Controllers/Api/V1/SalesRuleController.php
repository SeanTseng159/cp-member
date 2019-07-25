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
            $check_result = $this->service->checkCodeDiscount($discount);

            if ($check_result) {

                // 加入優惠碼至購物車
                $this->cartService->setAddDiscountCode($cart, $discount->code, $this->getMemberId());
            }

            $data = [
                ''
            ];



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
//                "spec": {
//                    "id": 377,
//                        "name": "test_spec"
//                    },
//                    "type": {
//                    "id": 430,
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
//        "totalAmount": 4,
//        "discountAmount": 0,
//        "discountTotalAmount": 4,
//        "shippingFee": 3,
//        "payAmount": 7,
//        "canCheckout": true,
//        "hasPhysical": true,
//        "promotion": null