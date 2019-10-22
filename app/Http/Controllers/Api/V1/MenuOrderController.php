<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Logger;
use App\Enum\WaitingStatus;
use App\Result\ShopWaitingResult;
use App\Services\MenuOrderService;
use App\Services\ShopWaitingService;
use App\Services\Ticket\DiningCarService;
use App\Services\Ticket\MemberDiningCarService;
use App\Traits\MemberHelper;
use App\Traits\ShopHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class MenuOrderController extends RestLaravelController
{

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

            $this->service->create($shopId,
                $data->menu,
                $data->payment,
                $data->cellphone,
                $data->time,
                $data->remarks,
                $request->memberId);


            return $this->success(true);
        } catch (\Exception $e) {
            Logger::error('MenuOrderController::create', $e->getMessage());
            return $this->failure('E0007', $e->getMessage());
        }


    }

}