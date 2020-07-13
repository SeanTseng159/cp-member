<?php
/**
 * User: lee
 * Date: 2020/07/09
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1\Guest;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Parameter\Guest\OrderParameter;
use App\Services\Ticket\GuestOrderService;
use App\Result\Ticket\OrderResult;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;


class OrderController extends RestLaravelController
{
    protected $service;

    public function __construct(GuestOrderService $service)
    {
        $this->service = $service;
    }

    /**
     * 訂單搜尋
     * @param $request
     * @return mixed
     */
    public function detail(Request $request)
    {
        try {
            $params = (new OrderParameter($request))->detail();

            $guestOrder = $this->service->findByPhone($params);

            if (!$guestOrder) return $this->failureCode('E0101');

            $result = (new OrderResult)->get($guestOrder->order, true, $guestOrder->name);

            return $this->success($result);
        } catch (Exception $e) {
            Logger::error('Guest order search Error', $e->getMessage());
            return $this->failureCode('E0101');
        }
    }
}
