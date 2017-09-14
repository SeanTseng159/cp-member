<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Order\OrderParameter;
use Ksd\Mediation\Parameter\Order\SearchParameter;
use Ksd\Mediation\Services\OrderService;

class OrderController extends RestLaravelController
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function info()
    {
        return $this->success($this->orderService->info());

    }

    public function items(Request $request, $itemId)
    {
        $parameter = new OrderParameter();
        $parameter->laravelRequest($itemId, $request);
        return $this->success($this->orderService->order($parameter));
    }

    public function search(Request $request)
    {
        $parameters = new SearchParameter();
        $parameters->laravelRequest($request);
        return $this->success($this->orderService->search($parameters));
    }


}
