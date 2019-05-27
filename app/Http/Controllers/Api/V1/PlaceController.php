<?php


namespace App\Http\Controllers\Api\V1;


use App\Services\AVR\LandmarkService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class PlaceController extends RestLaravelController
{
    protected $landmarkService;

    public function __construct(LandmarkService $landmarkService)
    {
        $this->landmarkService = $landmarkService;
    }


    public function icons(Request $request)
    {
        try {
            $hash = $request->input('hash');

            $data = $this->landmarkService->icons($hash);
            return $this->success($data);

        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }
}
