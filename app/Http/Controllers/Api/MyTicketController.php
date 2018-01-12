<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\MyTicketService;
use Ksd\Mediation\Parameter\MyTicket\InfoParameter;
use Ksd\Mediation\Parameter\MyTicket\QueryParameter;
use Ksd\Mediation\Parameter\MyTicket\CatalogIconParameter;
use App\Services\MemberService;

class MyTicketController extends RestLaravelController
{
    private $myTicketService;
    protected $memberService;

    public function __construct(MyTicketService $myTicketService, MemberService $memberService)
    {
        $this->myTicketService = $myTicketService;
        $this->memberService = $memberService;
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
        return $this->success($this->myTicketService->info($parameter));

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
        if ($data) {
            \Log::debug('=== ticket detail ===');
            \Log::debug(print_r($data, true));

            if (isset($data->gift) && $data->gift) {
                $member = $this->memberService->find($data->gift->memberId);

                if ($member) {
                    $memberData = new \stdClass;
                    $memberData->name = $memberData->name;
                    $memberData->phone = '+' . $memberData->countryCode . $memberData->cellphone;

                    $member = $memberData;
                }

                unset($data->gift->memberId);
                $data->gift->member = $member;
            }

            return $this->success($data);
        }

        return $this->failure('E0005', '資料無法取得');
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
        return ($result) ? $this->success() : $this->failure('E0003', '更新失敗');

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
        return ($result) ? $this->success() : $this->failure('E0003', '更新失敗');

    }


}
