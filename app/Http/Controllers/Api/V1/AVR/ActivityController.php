<?php


namespace App\Http\Controllers\Api\V1\AVR;


use App\Result\AVR\ActivityResult;
use App\Services\Ticket\ActivityService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class ActivityController extends RestLaravelController
{
    protected $service;

    public function __construct(ActivityService $service)
    {
        $this->service = $service;

    }


    public function list(Request $request)
    {

        try {
            $data = $this->service->list();
            $data = (new ActivityResult)->list($data);
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }

    


}
