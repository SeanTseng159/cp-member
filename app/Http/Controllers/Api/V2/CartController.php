<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V2;

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
            $result['info'] = $this->getCheckoutInfo($cart->hasPhysical, null, 'array');

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('Get buyNow info Error', $e->getMessage());
            return $this->failureCode('E9021');
        }
    }
}
