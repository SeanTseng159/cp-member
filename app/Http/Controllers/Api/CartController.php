<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

    public function info()
    {
        return $this->success($this->cartService->info());
    }

    public function detail()
    {
        return $this->success($this->cartService->detail());
    }

    public function add(Request $request)
    {
        $parameters = new ProductParameter();
        $parameters->laravelRequest($request);
        $this->cartService->add($parameters);
        return $this->success();
    }

    public function update(Request $request)
    {
        $parameters = new ProductParameter();
        $parameters->laravelRequest($request);
        $this->cartService->update($parameters);
        return $this->success();
    }

    public function delete(Request $request)
    {
        $parameters = new ProductParameter();
        $parameters->laravelRequest($request);
        $this->cartService->delete($parameters);
        return $this->success();
    }
}
