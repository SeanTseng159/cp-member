<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use Ksd\Mediation\Parameter\Cart\CartParameter as OldCartParameter;
use Ksd\Mediation\Services\CartService as OldCartService;

// new
use App\Parameter\CartParameter;

use App\Services\Ticket\ProductService;
use App\Services\CartService;
use App\Services\Ticket\OrderService;
use App\Services\Ticket\PromotionService;

use App\Result\CartResult;

use App\Traits\CartHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;


class CartController extends RestLaravelController
{
    use CartHelper;

    protected $oldCartService;

    protected $productService;
    protected $cartService;
    protected $orderService;
    protected $promotionService;

    public function __construct(OldCartService $oldCartService,
                                ProductService $productService,
                                CartService $cartService,
                                OrderService $orderService,
                                PromotionService $promotionService)
    {
        $this->oldCartService = $oldCartService;
        $this->productService = $productService;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->promotionService = $promotionService;
    }

    /**
     * 取得一次性購物車資訊並加入購物車(依來源)
     * @param $parameter
     * @return mixed
     */
    public function oneOff(Request $request)
    {
        $parameter = new OldCartParameter();
        $parameter->laravelRequest($request);

        $result = $this->oldCartService->oneOff($parameter);
        return $this->success($result);
    }

    /**
     * 立即購買
     * @param $request
     * @return mixed
     */
    public function buyNow(Request $request)
    {
        try {
            $param = (new CartParameter($request))->buyNow();

            // init value
            $isPhysical = false;

            // 檢查商品
            $products[0] = $this->productService->findByCheckout($param->productId, $param->specId, $param->specPriceId, true);

            // 檢查商品是否存在
            if (!$products[0]) return $this->failureCode('E9010');

            // 帶入購買數量
            $products[0]->quantity = $param->quantity;

            // 是否有實體商品
            if ($products[0]->is_physical) $isPhysical = true;

            // 檢查商品狀態, 是否可購買
            $statusCode = $this->checkProductStatus('buyNow', $products[0], $products[0]->quantity, $param->memberId);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 檢查加購商品
            if ($param->additionalProducts && is_array($param->additionalProducts)) {
                $prods = [];
                $checkStatusCode = '00000';
                foreach ($param->additionalProducts as $k => $product) {
                    // 檢查商品
                    $prods[$k] = $this->productService->findAdditionalByCheckout($product['id'], $product['specId'], $product['specPriceId'], true);

                    // 檢查商品是否存在
                    if (!$prods[$k]) return $this->failureCode('E9010');

                    // 帶入購買數量
                    $prods[$k]->quantity = $product['quantity'];

                    // 是否有實體商品
                    if ($prods[$k]->is_physical) $isPhysical = true;

                    // 檢查商品狀態, 是否可購買
                    $statusCode = $this->checkProductStatus('buyNow', $prods[$k], $prods[$k]->quantity, $param->memberId);
                    if ($statusCode !== '00000') {
                        $checkStatusCode = $statusCode;
                        $notEnoughStocks[] = $prods[$k]->prod_spec_price_id;
                    }
                }

                if ($checkStatusCode !== '00000') return $this->failureCode($checkStatusCode, $notEnoughStocks);

                $products[0]->purchase = $prods;
            }

            // 處理購物車格式
            $cart = (new CartResult)->get('buyNow', $products, true);
            // 加入快取購物車
            $this->cartService->add('buyNow', $param->memberId, serialize($cart));

            // 輸出簡化購物車
            $result['cart'] = (new CartResult)->simplify($cart);

            // 取付款方法
            $result['info'] = $this->getCheckoutInfo($isPhysical);

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('buyNow Error', $e->getMessage());
            return $this->failureCode('E9013');
        }
    }

    /**
     * 獨立賣場立即購買
     * @param $request
     * @return mixed
     */
    public function market(Request $request)
    {
        try {
            $params = (new CartParameter($request))->market();

            if (!$params->marketId) return $this->failureCode('E9100');
            if (!$params->products || !is_array($params->products)) return $this->failureCode('E9030');

            // 檢查所有商品狀態
            $checkStatusCode = '00000';
            $totalAmount = 0;
            $totalQuantity = 0;
            $isPhysical = false;
            foreach ($params->products as $k => $product) {
                // 取商品
                $prods[$k] = $this->promotionService->product($params->marketId, $product['id'], $product['specId'], $product['specPriceId'], true);
                // 檢查商品是否存在
                if (!$prods[$k]) return $this->failureCode('E9010');

                // 帶入購買數量
                $prods[$k]->quantity = $product['quantity'];

                // 是否有實體商品
                if ($prods[$k]->is_physical) $isPhysical = true;

                // 檢查商品狀態, 是否可購買
                $statusCode = $this->checkProductStatus('market', $prods[$k], $prods[$k]->quantity, $params->memberId);
                if ($statusCode !== '00000') {
                    $checkStatusCode = $statusCode;
                    $notEnoughStocks[] = $prods[$k]->prod_spec_price_id;
                }

                // 累積總價跟數量
                $totalAmount += $prods[$k]->prod_spec_price_value * $prods[$k]->quantity;
                $totalQuantity += $prods[$k]->quantity;
            }
            if ($checkStatusCode !== '00000') return $this->failureCode($checkStatusCode, $notEnoughStocks);


            // 檢查優惠是否符合
            // 取賣場資訊
            $promotion = $this->promotionService->find($params->marketId);
            $statusCode = $this->checkDiscountRule($promotion, $totalAmount, $totalQuantity);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 處理購物車格式
            $cart = (new CartResult)->get('market', $prods, true, $promotion);
            // 加入快取購物車
            $this->cartService->add('market', $params->memberId, serialize($cart));

            // 輸出簡化購物車
            $result['cart'] = (new CartResult)->simplify($cart);

            // 取付款方法
            $result['info'] = $this->getCheckoutInfo($isPhysical);

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('Market buyNow Error', $e->getMessage());
            return $this->failureCode('E9013');
        }
    }

    /**
     * 取立即購買 (購物車跟付款資訊)
     * @param $request
     * @return mixed
     */
    public function info(Request $request)
    {
        try {
            $param = (new CartParameter($request))->info();

            // 取購物車內容
            $cart = $this->cartService->find($param->action, $param->memberId);
            if (!$cart) return $this->failureCode('E9021');
            $cart = unserialize($cart);

            // 處理購物車格式
            $result['cart'] = (new CartResult)->simplify($cart);

            // 取付款方法
            $result['info'] = $this->getCheckoutInfo($cart->hasPhysical);

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('Get buyNow info Error', $e->getMessage());
            return $this->failureCode('E9021');
        }
    }
}
