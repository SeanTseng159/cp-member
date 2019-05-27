<?php


namespace App\Http\Controllers\Api\V1;


use App\Services\AVR\LandmarkService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class PlaceController extends RestLaravelController
{
    protected $landmarkService;
    protected $distance = 1000;//公尺

    public function __construct(LandmarkService $landmarkService)
    {
        $this->landmarkService = $landmarkService;

    }


    public function list(Request $request)
    {
        try {
            $lat = $request->input('lat');
            $lng = $request->input('lng');
            if (is_null($lat) or is_null($lng)) {
                throw new \Exception('E0001');
            }

            $distance = $request->input('distance');
            if (is_null($distance))
                $distance = $this->distance;

            $data = $this->landmarkService->aroundPlace($lat, $lng, $distance);
            return $this->success($data);

        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $data = $this->landmarkService->placeInfo($id);
            return $this->success($data);

        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }

    public function icons(Request $request)
    {
        dd($request);
        try {
            $hash = $request->input('hash');

            $data = $this->landmarkService->icons($hash);
            return $this->success($data);

        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }
}
