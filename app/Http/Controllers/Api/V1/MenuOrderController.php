<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Logger;
use App\Result\MenuOrderResult;
use App\Services\MenuOrderService;
use App\Services\Ticket\DiningCarService;
use App\Traits\MemberHelper;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class MenuOrderController extends RestLaravelController
{
    use MemberHelper;

    protected $service;
    protected $diningCarService;

    public function __construct(MenuOrderService $service, DiningCarService $diningCarService)
    {
        $this->service = $service;
        $this->diningCarService = $diningCarService;
    }


    public function create(Request $request, $shopId)
    {
        try {

            $data = $request->only([
                'menu',
                'payment',
                'cellphone',
                'time',
                'remarks'
            ]);

            $validator = \Validator::make($data, [
                'menu.*.id' => 'required|numeric',
                'menu.*.quantity' => 'required|numeric',
                'payment' => 'required|max:1',
                'cellphone' => 'required',
                'time' => 'required|date_format:Y-m-d H:i',
            ]);

            if ($validator->fails())
                throw new \Exception(join(' ', $validator->errors()->all()));


            $shop = $this->diningCarService->easyFind($shopId);

            if (!$shop)
                throw  new \Exception('店舖不存在');

            if (!$shop->canOrdering)
                throw  new \Exception("[{$shop->name}]尚未提供線上點餐");

            if (!$shop->employee->supplier->canEC)
                throw  new \Exception("[{$shop->name}]尚未提供線上購買");

            $data = (object)$data;
            $memberID = $this->getMemberId();

            $menuOrderId = $this->service->create($shopId,
                $data->menu,
                $data->payment,
                $data->cellphone,
                $data->time,
                $data->remarks,
                $memberID);

            $menuOrder = $this->service->get($menuOrderId);
            $this->service->sendSMS($shop->name,$menuOrder->menu_order_no,$menuOrder->code,$data->cellphone);

            $ret = (new MenuOrderResult)->get($menuOrder);
            return $this->success($ret);
        } catch (\Exception $e) {
            Logger::error('MenuOrderController::create', $e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }
    }

    public function detail(Request $request, $code)
    {
        try {
            $menuOrder = $this->service->getByCode($code);
            if (!$menuOrder)
                throw new \Exception('查無訂餐資料');

            $ret = (new MenuOrderResult)->get($menuOrder);

            return $this->success($ret);

        } catch (\Exception $e) {
            Logger::error('MenuOrderController::detail', $e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }
    }

    public function cancel(Request $request, $code)
    {
        try {
            $menuOrder = $this->service->getByCode($code);
            if (!$menuOrder)
                throw new \Exception("查無訂餐資料");
            $this->service->updateStatus($code, false);
            return $this->success();
        } catch (\Exception $e) {
            Logger::error('MenuOrderController::cancel', $e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }

    }

    public function memberList(Request $request)
    {

        try {
            $memberId = $request->memberId;

            $menuOrderList = $this->service->memberList($memberId);

            $ret = $menuOrderList->map(function ($item) {
                return (new MenuOrderResult)->get($item);
            });
            return $this->success($ret);
        } catch (\Exception $e) {
            Logger::error('MenuOrderController::memberList', $e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }
    }

    public function getQrCode(Request $request, $orderNo)
    {
        try {

            $menuOrder = $this->service->getByOrderNo($orderNo);
            if (!$menuOrder || !$menuOrder->order_id)
                throw new \Exception('查無訂餐資料');

            $qrcode = $menuOrder->qrcode;

            return $this->success(['code' => $qrcode]);
        } catch (\Exception $e) {
            Logger::error('MenuOrderController::memberList', $e->getMessage());
            return $this->failure('E0001', $e->getMessage());
        }
    }
}
