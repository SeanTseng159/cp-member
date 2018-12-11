<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Parameter\CheckoutParameter;

use App\Services\CheckoutService;
use App\Services\Ticket\ProductService;
use App\Services\CartService;
use App\Services\Ticket\PaymentMethodService;
use App\Services\Ticket\OrderService;
use App\Services\PaymentService;

use App\Result\CartResult;
use App\Result\PaymentInfoResult;

use App\Jobs\Mail\OrderCreatedMail;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;

class CheckoutController extends RestLaravelController
{
    protected $checkoutService;
    protected $productService;
    protected $cartService;
    protected $orderService;
    protected $paymentService;

    public function __construct(CheckoutService $checkoutService,
                                ProductService $productService,
                                CartService $cartService,
                                OrderService $orderService,
                                PaymentService $paymentService)
    {
        $this->checkoutService = $checkoutService;
        $this->productService = $productService;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * 立即購買
     * @param $request
     * @return mixed
     */
    public function buyNow(Request $request)
    {
        try {
            $param = (new CheckoutParameter($request))->buyNow();

            // 取商品
            $product = $this->productService->findByCheckout($param->productId, $param->specId, $param->specPriceId, true);
            // 檢查商品是否存在
            if (!$product) return $this->failureCode('E9010');

            // 帶入購買數量
            $product->quantity = $param->quantity;

            // 檢查商品狀態, 是否可購買
            $statusCode = $this->checkProductStatus($product, $param->memberId);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 處理購物車格式
            $cart = (new CartResult)->get([$product], true);
            // 加入快取購物車
            $this->cartService->add('oneOff', $param->memberId, serialize($cart));

            // 輸出簡化購物車
            $result['cart'] = (new CartResult)->simplify($cart);

            // 取付款方法
            $paymentMethodService = app()->build(PaymentMethodService::class);
            $all = $paymentMethodService->all();
            $result['info']['payments'] = (new PaymentInfoResult)->getPayments($all);
            // 取付款方式
            $result['info']['shipments'] = (new PaymentInfoResult)->getShipments($product->is_physical);
            // 取發票方式
            $result['info']['billings'] = (new PaymentInfoResult)->getBillings();

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('buyNow Error', $e->getMessage());
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
            $param = (new CheckoutParameter($request))->info();

            // 取購物車內容
            $cart = $this->cartService->find('oneOff', $param->memberId);
            if (!$cart) return $this->failureCode('E9021');
            $cart = unserialize($cart);

            // 處理購物車格式
            $result['cart'] = (new CartResult)->simplify($cart);

            // 取付款方法
            $paymentMethodService = app()->build(PaymentMethodService::class);
            $all = $paymentMethodService->all();
            $result['info']['payments'] = (new PaymentInfoResult)->getPayments($all);
            // 取付款方式
            $result['info']['shipments'] = (new PaymentInfoResult)->getShipments($cart->hasPhysical);
            // 取發票方式
            $result['info']['billings'] = (new PaymentInfoResult)->getBillings();

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('Get buyNow info Error', $e->getMessage());
            return $this->failureCode('E9021');
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
            $params = (new CheckoutParameter($request))->market();

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

                // 檢查商品狀態, 是否可購買
                $statusCode = $this->checkProductStatus($prods[$k], $params->memberId);
                if ($statusCode !== '00000') {
                    $checkStatusCode = $statusCode;
                    $notEnoughStocks[] = $prods[$k]->prod_id;
                }

                // 是否有實體商品
                if ($prods[$k]->is_physical) $isPhysical = true;

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
            $paymentMethodService = app()->build(PaymentMethodService::class);
            $all = $paymentMethodService->all();
            $result['info']['payments'] = (new PaymentInfoResult)->getPayments($all);
            // 取付款方式
            $result['info']['shipments'] = (new PaymentInfoResult)->getShipments($isPhysical);
            // 取發票方式
            $result['info']['billings'] = (new PaymentInfoResult)->getBillings();

            return $this->success($result);
        } catch (Exception $e) {
            var_dump($e->getMessage());
            // Logger::error('Market buyNow Error', $e->getMessage());
            // return $this->failureCode('E9013');
        }
    }

    /**
     * 結帳
     * @param $request
     * @return mixed
     */
    public function payment(Request $request)
    {
        try {
            $params = (new CheckoutParameter($request))->payment();

            // 取購物車內容
            if ($params->action === 'buyNow') $cartName = 'oneOff';
            elseif ($params->action === 'market') $cartName = 'market';

            $cart = $this->cartService->find($cartName, $params->memberId);
            $cart = unserialize($cart);

            // 檢查購物車內所有狀態是否可購買
            $statusCode = $this->checkCartStatus($cart, $params->memberId);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 成立訂單
            $order = $this->orderService->create($params, $cart);
            if (!$order) throw new CustomException('E9001');

            // 寄送訂單成立通知信
            dispatch(new OrderCreatedMail($params->memberId, 'ct_pass', $order->order_no))->onQueue('high')->delay(5);

            // 處理金流
            $payParams = [
                'memberId' => $params->memberId,
                'orderNo' => $order->order_no,
                'payAmount' => $order->order_amount,
                'itemsCount' => $order->order_items,
                'device' => $params->deviceName,
                'hasLinePayApp' => $params->hasLinePayApp
            ];
            $result = $this->paymentService->payment($params->payment, $payParams);

            // 刪除購物車
            $this->cartService->delete('oneOff', $params->memberId);

            return $this->success($result);
        } catch (CustomException $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode($e->getMessage());
        } catch (Exception $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode('E9006');
        }
    }

    /**
     * 重新結帳
     * @param $request
     * @param $no
     * @return mixed
     */
    public function repay(Request $request, $orderNo)
    {
        try {
            if (!$orderNo) return $this->failureCode('E9016');

            // 檢查訂單是否可付款
            $order = $this->orderService->findCanPay($orderNo);
            if (!$order) return $this->failureCode('E9016');

            // 檢查付款人
            if ($order->member_id !== $request->memberId) return $this->failureCode('E9017');

            $params = (new CheckoutParameter($request))->repay();

            // 信用卡資訊寫入DB
            if ($params->payment['gateway'] === '3' && $params->payment['method'] === '111') {
                $updateResult = $this->orderService->updateCC($orderNo, $params->payment);
                if (!$updateResult) return $this->failureCode('E9015');
            }

            // 處理金流
            $payParams = [
                'memberId' => $params->memberId,
                'orderNo' => (string) $order->order_no,
                'payAmount' => $order->order_amount,
                'itemsCount' => $order->order_items,
                'device' => $params->deviceName,
                'hasLinePayApp' => $params->hasLinePayApp
            ];
            $result = $this->paymentService->payment($params->payment, $payParams);

            return $this->success($result);
        } catch (CustomException $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode($e->getMessage());
        } catch (Exception $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode('E9006');
        }
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
        if ($product->prod_limit_type === 1) {
            $memberBuyQuantity = $this->orderService->getCountByProdAndMember($product->product_id, $memberId);
            $buyQuantity += $memberBuyQuantity;
        }
        if ($buyQuantity > $product->prod_limit_num) return 'E9012';

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
        $value1 = 3;
        $value2 = 2499;

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
