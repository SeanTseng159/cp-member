<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\MyTicketService;
use Ksd\Mediation\Parameter\MyTicket\InfoParameter;
use Ksd\Mediation\Parameter\MyTicket\QueryParameter;
use Ksd\Mediation\Parameter\MyTicket\CatalogIconParameter;

class MyTicketController extends RestLaravelController
{
    private $myTicketService;

    public function __construct(MyTicketService $myTicketService)
    {
        $this->myTicketService = $myTicketService;
    }

    /**
     * 票券物理主分類(目錄)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function catalogIcon(Request $request)
    {
        $parameter = new CatalogIconParameter();
        $parameter->laravelRequest($request);
        return $this->success($this->myTicketService->catalogIcon($parameter));

    }

    /**
     * 取得票券使用說明
     * @return \Illuminate\Http\JsonResponse
     */
    public function help()
    {
        return $this->success($this->myTicketService->help());

    }

    /**
     * 取得票券列表
     * @param Request $request
     * @param $statusId
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request, $statusId)
    {
        $parameter = new InfoParameter();
        $parameter->laravelRequest($statusId, $request);
        $data = $this->myTicketService->info($parameter);

        return ($data !== 'nodata') ? $this->success($data) : $this->failureCode('E0005');
    }


    /**
     * 取得票券明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, $id)
    {
        $parameter = new InfoParameter();
        $parameter->laravelRequest($id, $request);
        $data = $this->myTicketService->detail($parameter);

        return ($data !== 'nodata') ? $this->success($data) : $this->failureCode('E0005');
    }


    /**
     * 利用票券id取得使用紀錄
     * @param Request $request
     * @param $ticketId
     * @return \Illuminate\Http\JsonResponse
     */
    public function record(Request $request, $ticketId)
    {
        $parameter = new InfoParameter();
        $parameter->laravelRequest($ticketId, $request);
        return $this->success($this->myTicketService->record($parameter));

    }

    /**
     * 轉贈票券
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gift(Request $request)
    {
        $parameters = new QueryParameter();
        $parameters->laravelRequest($request);
        $result = $this->myTicketService->gift($parameters);

        if ($result === 1) return $this->success();
        else if ($result === 2) return $this->failureCode('E0061');
        else if ($result === 3) return $this->failureCode('E4002');
        else return $this->failureCode('E4003');
    }

    /**
     * 轉贈票券退回
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refund(Request $request)
    {
        $parameters = new QueryParameter();
        $parameters->laravelRequest($request);
        $result = $this->myTicketService->refund($parameters);
        return ($result) ? $this->success() : $this->failureCode('E4011');

    }


    /**
     * 票券隱藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function hide(Request $request)
    {
        $parameters = new QueryParameter();
        $parameters->laravelRequest($request);
        $result = $this->myTicketService->hide($parameters);
        return ($result) ? $this->success() : $this->failureCode('E4004');

    }


}
