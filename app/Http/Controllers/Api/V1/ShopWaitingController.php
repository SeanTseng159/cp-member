<?php

namespace App\Http\Controllers\Api\V1;

use App\Result\ShopWaitingResult;
use App\Services\ShopWaitingService;
use App\Traits\MemberHelper;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

class ShopWaitingController extends RestLaravelController
{
    use MemberHelper;

    private $service;

    public function __construct(ShopWaitingService $service)
    {
        $this->service = $service;
    }

    public function info(Request $request, $id)
    {

        try {
            $waiting = $this->service->find($id);
            $data = (new ShopWaitingResult())->info($waiting);

            return $this->success($data);
        } catch (\Exception $e) {
            dd($e);
            return $this->failureCode('');
        }


    }

    public function create(Request $request)
    {

    }

    public function get(Request $request)
    {

    }

    public function delete(Request $request)
    {

    }

}
