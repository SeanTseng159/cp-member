<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

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
     * 根據 id 取得商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function info(Request $request)
    {
        // magento
        $magentoOrderService = app()->build(MagentoOrderService::class);
        $magentoOrders = $magentoOrderService->magentoInfo();

        // citypass
        $parameter = new InfoParameter($request);
        $data = $this->orderService->getMemberOrdersByDate($parameter->memberId, $parameter->startDate, $parameter->endDate);
        $ticketOrders = (new OrderResult)->getAll($data, true);

        $data = array_merge($magentoOrders, $ticketOrders);
        $result = ($data) ? $this->multiArraySort($data, 'orderDate') : null;

        return $this->success($result);
    }
}
