<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\LinePayMapService;
use App\Parameter\LinePayMapParameter;

class LinePayMapController extends RestLaravelController
{
    protected $service;

    public function __construct(LinePayMapService $service)
    {
        $this->service = $service;
    }
    
    
    public function stores(Request $request)
    {
        $pamams = (new LinePayMapParameter())->stores($request);
        return $this->success(['stores' => $this->service->getStores($pamams['longitude'], $pamams['latitude'])]);
    }
    
}
