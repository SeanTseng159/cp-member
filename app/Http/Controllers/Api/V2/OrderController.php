<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Exception;
use App\Core\Logger;

use App\Services\Ticket\OrderService;
use App\Parameter\Ticket\Order\InfoParameter;
use App\Result\Ticket\OrderResult;

use App\Traits\ObjectHelper;

// magento
use Ksd\Mediation\Services\OrderService as MagentoOrderService;

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
    public function info(Request $request, MagentoOrderService $magentoOrderService)
    {
        try {
            // magento
            $magentoOrders = $magentoOrderService->magentoInfo(true);

            // citypass
            $params = (new InfoParameter($request))->info();
            $data = $this->orderService->getMemberOrdersByDate($params);
            $ticketOrders = (new OrderResult)->getAll($data);

            $data = array_merge($magentoOrders, $ticketOrders);
            $result = ($data) ? $this->multiArraySort($data, 'orderDate') : null;

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('V2 Order Info Error', $e->getMessage());
            return $this->failureCode('E0103');
        }
    }

    /**
     * 根據 id 取得訂單
     * @param Request $request
     * @param $orderNo
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, $orderNo, MagentoOrderService $magentoOrderService)
    {
        try {
            if (!$orderNo) return $this->failureCode('E0101');

            // magento
            if ((substr($orderNo, 0, 5) === 'M0000')) {
                $params = new \stdClass;
                $params->source = 'magento';
                $params->id = str_replace('M0000', '', $orderNo);
                $result = $magentoOrderService->find($params, true);
                if (!$result) return $this->failureCode('E0101');
                $result = $result[0];
            }
            else {
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
