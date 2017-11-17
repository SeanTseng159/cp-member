<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\SalesRule\CouponParameter;
use Ksd\Mediation\Services\SalesRuleService;

class SalesRuleController extends RestLaravelController
{
    private $service;

    public function __construct(SalesRuleService $service)
    {
        $this->service = $service;
    }

    /**
     * 使用折扣優惠
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCoupon(Request $request)
    {
        $parameters = new CouponParameter();
        $parameters->laravelRequest($request);
        $salesRule = $this->service->addCoupon($parameters);
        return $this->success($salesRule);
    }

    /**
     * 取消折扣優惠
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCoupon(Request $request)
    {
        $parameters = new CouponParameter();
        $parameters->laravelRequest($request);
        $this->service->deleteCoupon($parameters);
        return $this->success();
    }
}
