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

        $returnObj = new \stdClass();
        $returnObj->code = ($salesRule['statusCode'] == 201) ? '00000' : 'E00002';
        $returnObj->message = ($returnObj->code == '00000') ? 'success' : $salesRule['data'];

        return ($returnObj->code == '00000') ? $this->success($salesRule['data']) : $this->failure($returnObj->code, $returnObj->message);
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

        $salesRule = $this->service->deleteCoupon($parameters);


        $returnObj = new \stdClass();
        $returnObj->code = ($salesRule['statusCode'] == 203) ? '00000' : 'E00002';
        $returnObj->message = ($returnObj->code == '00000') ? 'success' : $salesRule['data'];

        return ($returnObj->code == '00000') ? $this->success() : $this->failure($returnObj->code, $returnObj->message);

    }
}
