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
use App\Services\OneOffCartService;
use App\Services\Ticket\PaymentMethodService;
use App\Services\Ticket\OrderService;

use App\Result\CartResult;
use App\Result\PaymentInfoResult;

class CheckoutController extends RestLaravelController
{
    protected $checkoutService;
    protected $productService;
    protected $oneOffCartService;
    protected $orderService;

    public function __construct(CheckoutService $checkoutService,
                                ProductService $productService,
                                OneOffCartService $oneOffCartService,
                                OrderService $orderService)
    {
        $this->checkoutService = $checkoutService;
        $this->productService = $productService;
        $this->oneOffCartService = $oneOffCartService;
        $this->orderService = $orderService;
    }

    /**
     * 立即購買
     * @param $parameter
     * @return mixed
     */
    public function buyNow(Request $request)
    {
        $param = (new CheckoutParameter($request))->buyNow();

        // 取商品
        $product = $this->productService->findByCheckout($param->productId, $param->specId, $param->specPriceId, true);
        // 檢查商品是否存在
        if (!$product) return $this->failureCode('E9010');

        // 帶入購買數量
        $product->quantity = $buyQuantity = $param->quantity;

        // 檢查限購數量
        if ($product->prod_limit_type === 1) {
            // $memberBuyQuantity = $this->orderService->getCountByProdAndMember($param->productId, $param->memberId);
            $buyQuantity += $product->quantity;
        }

        if ($buyQuantity > $product->prod_limit_num) return $this->failureCode('E9012');

        // 檢查是否有庫存
        if ($product->prod_spec_price_stock <= 0 && $product->prod_spec_price_stock >= $product->quantity) return $this->failureCode('E9011');

        $result['cart'] = (new CartResult)->get([$product]);
        // 加入快取購物車
        $this->oneOffCartService->add($param->memberId, $result['cart']);

        // 取付款方法
        $paymentMethodService = app()->build(PaymentMethodService::class);
        $all = $paymentMethodService->all();
        $result['info']['payments'] = (new PaymentInfoResult)->getPayments($all);
        // 取付款方式
        $result['info']['shipments'] = (new PaymentInfoResult)->getShipments($product->is_physical);
        // 取發票方式
        $result['info']['billings'] = (new PaymentInfoResult)->getBillings();

        return $this->success($result);
    }
}
