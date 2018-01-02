<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Service\ServiceParameter;
use Ksd\Mediation\Services\ServiceService;

class ServiceController extends RestLaravelController
{
    private $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * 取得常用問題
     * @return \Illuminate\Http\JsonResponse
     */
    public function qa()
    {
        return $this->success($this->serviceService->qa());
    }

    /**
     * 問題與建議
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestion(Request $request)
    {
        $parameters = new ServiceParameter();
        $parameters->laravelRequest($request);
        $result = $this->serviceService->suggestion($parameters);
        return ($result) ? $this->success() : $this->failure('E0002', '新增失敗');
    }


}
