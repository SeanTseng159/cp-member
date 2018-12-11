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
        try {
            // citypass
            $params = (new InfoParameter($request))->info();
            $data = $this->orderService->getMemberOrdersByDate($params);
            $result = (new OrderResult)->getAll($data);

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('V2 Order Info Error', $e->getMessage());
            return $this->failureCode('E0103');
        }
    }
}
