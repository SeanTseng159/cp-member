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

class CheckoutController extends RestLaravelController
{
    protected $checkoutService;
    protected $productService;

    public function __construct(CheckoutService $checkoutService, ProductService $productService)
    {
        $this->checkoutService = $checkoutService;
        $this->productService = $productService;
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
        $product = $this->productService->findByCheckout($param->productId, $param->specId, $param->specPriceId);
        // 檢查商品是否存在
        if (!$product) return $this->failureCode('E9010');

        // 檢查是否有庫存
        if ($product->prod_spec_price_stock <= 0) return $this->failureCode('E9011');

        //var_dump($product);
        //$result = $this->cartService->oneOff($parameter);
        //return $this->success($result);
    }
}
