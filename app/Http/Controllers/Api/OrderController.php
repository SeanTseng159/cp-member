<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Order\FindParameter;
use Ksd\Mediation\Parameter\Order\OrderParameter;
use Ksd\Mediation\Parameter\Order\SearchParameter;
use Ksd\Mediation\Parameter\Order\WriteoffParameter;
use Ksd\Mediation\Parameter\Order\UpdateParameter;
use Ksd\Mediation\Services\OrderService;

class OrderController extends RestLaravelController
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * 取得所有訂單列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        return $this->success($this->orderService->info());

    }

    /**
     * 根據訂單id 取得訂單細項資訊
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function items(Request $request, $itemId)
    {
        $parameter = new OrderParameter();
        $parameter->laravelRequest($itemId, $request);
        return $this->success($this->orderService->order($parameter));
    }

    /**
     * 根據 條件篩選 取得訂單
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $parameters = new SearchParameter();
        $parameters->laravelRequest($request);
        return $this->success($this->orderService->search($parameters));
    }

    /**
     * 根據 id 查詢訂單
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request, $id)
    {
        $parameters = new FindParameter();
        $parameters->laravelRequest($id, $request);
        return $this->success($this->orderService->find($parameters));
    }

    /**
     * 接收ATM繳款通知程式
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function writeoff(Request $request)
    {
        $parameters = new WriteoffParameter();
        $parameters->laravelRequest($request);
        $this->orderService->writeoff($parameters);
        return $this->success();
    }

    /**
     * 更新訂單狀態
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $parameters = new UpdateParameter();
        $parameters->laravelRequest($request);
        $result = $this->orderService->update(null,$parameters);
        return ($result) ? $this->success() : $this->failure('E0003', '更新失敗');
    }

}
