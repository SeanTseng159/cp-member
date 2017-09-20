<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Checkout\ConfirmParameter;
use Ksd\Mediation\Services\CheckoutService;

class CheckoutController extends RestLaravelController
{
    protected $service;

    public function __construct(CheckoutService $service)
    {
        $this->service = $service;
    }

    /**
     * 取得結帳資訊
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        return $this->success($this->service->info());
    }

    /**
     * 確定結帳
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(Request $request)
    {
        $parameters = new ConfirmParameter();
        $parameters->laravelRequest($request);
        $this->service->confirm($parameters);
        return $this->success();
    }
}
