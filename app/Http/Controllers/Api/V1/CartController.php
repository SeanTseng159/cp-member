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
use App\Services\Ticket\PaymentMethodService;
use App\Services\Ticket\OrderService;

use App\Result\CartResult;
use App\Result\PaymentInfoResult;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;


class CartController extends RestLaravelController
{
    protected $oldCartService;

    protected $productService;
    protected $cartService;
    protected $orderService;

    public function __construct(OldCartService $oldCartService,
                                ProductService $productService,
                                CartService $cartService,
                                OrderService $orderService)
    {
        $this->oldCartService = $oldCartService;
        $this->productService = $productService;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
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
            $statusCode = $this->checkProductStatus($products[0], $param->memberId);
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
                    $statusCode = $this->checkProductStatus($prods[$k], $param->memberId);
                    if ($statusCode !== '00000') {
                        $checkStatusCode = $statusCode;
                        $notEnoughStocks[] = $prods[$k]->prod_spec_price_id;
                    }
                }

                if ($checkStatusCode !== '00000') return $this->failureCode($checkStatusCode, $notEnoughStocks);

                $products[0]->purchase = $prods;
            }

            // 處理購物車格式
            $cart = (new CartResult)->get($products, true);
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
                $prods[$k] = $this->productService->findByCheckout($product['id'], $product['specId'], $product['specPriceId'], true);
                // 檢查商品是否存在
                if (!$prods[$k]) return $this->failureCode('E9010');

                // 帶入購買數量
                $prods[$k]->quantity = $product['quantity'];

                // 是否有實體商品
                if ($prods[$k]->is_physical) $isPhysical = true;

                // 檢查商品狀態, 是否可購買
                $statusCode = $this->checkProductStatus($prods[$k], $params->memberId);
                if ($statusCode !== '00000') {
                    $checkStatusCode = $statusCode;
                    $notEnoughStocks[] = $prods[$k]->prod_spec_price_id;
                }

                $totalAmount += $prods[$k]->prod_spec_price_value;
                $totalQuantity += $prods[$k]->quantity;
            }
            if ($checkStatusCode !== '00000') return $this->failureCode($checkStatusCode, $notEnoughStocks);


            // 檢查優惠是否符合
            $statusCode = $this->checkDiscountRule([], $totalAmount, $totalQuantity);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 處理購物車格式
            $cart = (new CartResult)->get($prods, true);
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

    /**
     * 檢查購物車內商品狀態、金額、數量
     * @param $isPhysical
     * @return array
     */
    private function getCheckoutInfo($isPhysical)
    {
        $paymentMethodService = app()->build(PaymentMethodService::class);
        $all = $paymentMethodService->all();

        $result['payments'] = (new PaymentInfoResult)->getPayments($all);
        // 取付款方式
        $result['shipments'] = (new PaymentInfoResult)->getShipments($isPhysical);
            // 取發票方式
        $result['billings'] = (new PaymentInfoResult)->getBillings();

        return $result;
    }

    /**
     * 檢查購物車內商品狀態、金額、數量
     * @param $cart
     * @param $memberId
     * @return mixed
     */
    private function checkCartStatus($cart, $memberId)
    {
        if (!$cart) return 'E9030';

        // 檢查購物車內商品狀態
        $statusCode = $this->checkCartProductStatus($cart->items, $memberId);
        if ($statusCode !== '00000') return $statusCode;

        // 檢查數量
        if ($cart->totalQuantity <= 0) return 'E9031';

        // 檢查金額
        if ($cart->payAmount <= 0) return 'E9032';

        return '00000';
    }

    /**
     * 檢查購物車內商品狀態, 是否可購買
     * @param $product
     * @param $memberId
     * @param $isPurchase
     * @return mixed
     */
    private function checkCartProductStatus($products, $memberId, $isPurchase = false)
    {
        if (!$isPurchase && !$products) return 'E9030';

        foreach ($products as $product) {
            $prod = $this->productService->findByCheckout($product->id, $product->additional->spec->id, $product->additional->type->id);
            $statusCode = $this->checkProductStatus($prod, $memberId);
            if ($statusCode !== '00000') return $statusCode;

            // 處理加購
            if (!$isPurchase && $product->purchase) {
                $statusCode = $this->checkCartProductStatus($product->purchase, $memberId, true);
                if ($statusCode !== '00000') return $statusCode;
            }
        }

        return '00000';
    }

    /**
     * 檢查商品狀態, 是否可購買
     * @param $product
     * @param $memberId
     * @return mixed
     */
    private function checkProductStatus($product, $memberId)
    {
        // 檢查限購數量
        $buyQuantity = $product->quantity;
        if ($product->prod_type === 1 || $product->prod_type === 2) {
            if ($product->prod_limit_type === 1) {
                $memberBuyQuantity = $this->orderService->getCountByProdAndMember($product->product_id, $memberId);
                $buyQuantity += $memberBuyQuantity;
            }
            if ($buyQuantity > $product->prod_limit_num) return 'E9012';
        }
        elseif ($product->prod_type === 3) {
            if ($buyQuantity > $product->prod_plus_limit) return 'E9012';
        }

        // 檢查是否有庫存
        if ($product->prod_spec_price_stock <= 0 && $product->prod_spec_price_stock >= $product->quantity) return 'E9011';

        return '00000';
    }

    /**
     * 檢查優惠是否符合
     * @param $rule
     * @param $totalAmount
     * @param $totalQuantity
     * @return mixed
     */
    private function checkDiscountRule($rule = [], $totalAmount = 0, $totalQuantity = 0)
    {
        $ype = 'FQFP';
        $value1 = 2;
        $value2 = 499;

        if ($ype === 'FQFP' && $totalQuantity !== $value1) {
            return 'E9201';
        }
        elseif (($ype === 'DQFP' || $ype === 'DQFD') && $totalQuantity < $value1) {
            return 'E9202';
        }
        elseif (($ype === 'DPFQ' || $ype === 'DPFD') && $totalAmount < $value1) {
            return 'E9203';
        }

        return '00000';
    }
}
