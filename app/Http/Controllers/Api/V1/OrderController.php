<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;
use App\Core\Logger;

use App\Services\Ticket\OrderService;
use App\Parameter\Ticket\Order\InfoParameter;
use App\Result\Ticket\OrderResult;

use Ksd\Mediation\Services\OrderService as MagentoOrderService;

use App\Traits\ObjectHelper;

class OrderController extends RestLaravelController
{
    use ObjectHelper;

    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * 取得訂單列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        // magento
        $magentoOrderService = app()->build(MagentoOrderService::class);
        $magentoOrders = $magentoOrderService->magentoInfo();

        // citypass
        $params = (new InfoParameter($request))->info();
        $data = $this->orderService->getMemberOrdersByDate($params);
        $ticketOrders = (new OrderResult)->getAllByV1($data);

        $data = array_merge($magentoOrders, $ticketOrders);
        $result = ($data) ? $this->multiArraySort($data, 'orderDate') : null;

        return $this->success($result);
    }

    /**
     * 搜尋訂單
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // citypass
        $params = (new InfoParameter($request))->search();
        $data = $this->orderService->getMemberOrdersByDate($params);
        $orders = (new OrderResult)->getAll($data);

        return $this->success($orders);
    }

    /**
     * 根據 id 取得訂單
     * @param Request $request
     * @param $orderNo
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, $orderNo)
    {
        try {
            $source = $request->input('source');
            if (!$orderNo || !$source) return $this->failureCode('E0101');
            // magento
            if ($source === 'magento') {
                $magentoOrderService = app()->build(MagentoOrderService::class);
                $params = new \stdClass;
                $params->source = $source;
                $params->id = $orderNo;
                $result = $magentoOrderService->find($params);
                if (!$result) return $this->failureCode('E0101');
                $result = $result[0];
            }
            // citypass
            elseif ($source === 'ct_pass') {
                $order = $this->orderService->findCanShowByOrderNo($request->memberId, $orderNo);
                if (!$order) return $this->failureCode('E0101');

                // 檢查付款人
                if ($order->member_id !== $request->memberId) return $this->failureCode('E9050');

                $result = (new OrderResult)->get($order, true);
            }

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('order detail Error', $e->getMessage());
            return $this->failureCode('E0101');
        }
    }
}
