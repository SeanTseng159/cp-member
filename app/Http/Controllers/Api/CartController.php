<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Cart\ProductParameter;
use Ksd\Mediation\Services\CartService;

class CartController extends RestLaravelController
{
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 取得購物車簡易資訊
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        return $this->success($this->cartService->info());
    }

    /**
     * 取得購物車資訊
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        return $this->success($this->cartService->detail());
    }

    /**
     * 增加商品至購物車
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $parameters = new ProductParameter();
        $parameters->laravelRequest($request);
        $result = $this->cartService->add($parameters);
        return ($result) ? $this->success() : $this->failure('E0002', '新增失敗');
    }

    /**
     * 更新購物車內商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $parameters = new ProductParameter();
        $parameters->laravelRequest($request);
        $result = $this->cartService->update($parameters);
        return ($result) ? $this->success() : $this->failure('E0003', '更新失敗');
    }

    /**
     * 刪除購物車內商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $parameters = new ProductParameter();
        $parameters->laravelRequest($request);
        $result = $this->cartService->delete($parameters);
        return ($result) ? $this->success() : $this->failure('E0004', '刪除失敗');
    }
}
