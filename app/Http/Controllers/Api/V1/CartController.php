<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use Ksd\Mediation\Parameter\Cart\CartParameter;
use Ksd\Mediation\Services\CartService;


class CartController extends RestLaravelController
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 取得一次性購物車資訊並加入購物車(依來源)
     * @param $parameter
     * @return mixed
     */
    public function oneOff(Request $request)
    {
        $parameter = new CartParameter();
        $parameter->laravelRequest($request);

        $result = $this->cartService->oneOff($parameter);
        return $this->success($result);
    }
}
