<?php
/**
 * User: lee
 * Date: 2020/07/09
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1\Guest;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Parameter\Guest\CheckoutParameter;
use App\Services\Ticket\ProductService;
use App\Services\CartService;
use App\Services\Ticket\ShippingFeeDetailService;

use App\Result\CartResult;
use App\Traits\CartHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;


class CheckoutController extends RestLaravelController
{
    use CartHelper;

    protected $productService;
    protected $cartService;

    public function __construct(ProductService $productService,
                                CartService $cartService)
    {
        $this->productService = $productService;
        $this->cartService = $cartService;
    }

    /**
     * 檢查購物車內容跟取付款資訊
     * @param $request
     * @return mixed
     */
    public function info(Request $request, ShippingFeeDetailService $shippingFeeDetailService)
    {
        try {
            $params = (new CheckoutParameter($request))->info();

            // 檢查所有商品狀態
            $checkStatusCode = '00000';
            $totalAmount = 0;
            $totalQuantity = 0;
            $hasPhysical = false;
            foreach ($params->products as $k => $product) {
                // 取商品
                $prods[$k] = $this->productService->findByCheckout2($product['id'], $product['specId'], $product['specPriceId']);

                // 檢查商品是否存在
                if (!$prods[$k]) return $this->failureCode('E9010');

                // 帶入購買數量
                $prods[$k]->quantity = $product['quantity'];

                // 是否有實體商品
                if ($prods[$k]->is_physical) $hasPhysical = true;

                // 檢查商品狀態, 是否可購買
                $statusCode = $this->checkProductStatus('guest', $prods[$k], $prods[$k]->quantity);
                if ($statusCode !== '00000') {
                    $checkStatusCode = $statusCode;
                    $notEnoughStocks[] = $prods[$k]->prod_spec_price_id;
                }

                // 累積總價跟數量
                $totalAmount += $prods[$k]->prod_spec_price_value * $prods[$k]->quantity;
                $totalQuantity += $prods[$k]->quantity;
            }

            // 取運費
            $shippingFeeDetail = ($hasPhysical) ? $shippingFeeDetailService->findBySupplierId($params->supplierId) : null;

            // 處理購物車格式
            $cart = (new CartResult)->getCartDetail('guest', $prods, $shippingFeeDetail);

            // 加入快取購物車
            $this->cartService->add('guest', md5($params->token), serialize($cart));

            // 輸出簡化購物車
            $result['cart'] = (new CartResult)->simplify($cart);

            // 取付款方法
            $result['info'] = $this->getCheckoutInfo($hasPhysical, null, 'array');

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('Guest cart info Error', $e->getMessage());
            return $this->failureCode('E9021');
        }
    }
}
