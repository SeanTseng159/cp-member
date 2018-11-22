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

use App\Result\CartResult;

class CheckoutController extends RestLaravelController
{
    protected $checkoutService;
    protected $productService;
    protected $oneOffCartService;

    public function __construct(CheckoutService $checkoutService, ProductService $productService, OneOffCartService $oneOffCartService)
    {
        $this->checkoutService = $checkoutService;
        $this->productService = $productService;
        $this->oneOffCartService = $oneOffCartService;
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

        // 檢查是否有庫存
        if ($product->prod_spec_price_stock <= 0) return $this->failureCode('E9011');

        // 帶入購買數量
        $product->quantity = $param->quantity;

        /*$result['cart'] = (new CartResult)->get([$product]);

        // 加入購物車
        $this->oneOffCartService->add($param->memberId, $result['cart']);


        $result['info'] = (new CartResult)->get([$product]);*/



        //return $this->success($result);
    }
}
