<?php


namespace App\Http\Controllers\Api\V1\AVR;



use App\Result\AVR\ActivityResult;
use App\Services\AVR\ActivityService;
use App\Services\AVR\MissionService;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class ActivityController extends RestLaravelController
{
    protected $activityService;
    protected $missionService;

    public function __construct(ActivityService $service,MissionService $missionService)
    {
        $this->activityService = $service;
        $this->missionService = $missionService;

    }


    public function list(Request $request)
    {

        try {
            $data = $this->activityService->list();
            $data = (new ActivityResult)->list($data);
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }

    public function detail(Request $request, $activityId)
    {

        try {

            $memberID = $request->memberId;

            if (!$activityId) {
                throw new \Exception('E0001');
            }

            $data = $this->activityService->detail($activityId);
            if(!$data)
                return $this->success();
            $data = (new ActivityResult)->activityDetail($data);
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }


    public function missionList(Request $request, $activityId)
    {

        try {

            if (!$activityId) {
                throw new \Exception('E0001');
            }

            $data = $this->activityService->detail($activityId);
            if(!$data)
                return $this->success();
            $data = (new ActivityResult)->missionList($data);
            return $this->success($data);
        } catch (\Exception $e) {
            dd($e);
            return $this->failureCode('E0001');
        }
    }

    public function missionDetail(Request $request, $missionId)
    {

        try {
            $memberID = $request->memberId;
            $data = $this->missionService->detail($missionId);
            return $this->success($data);


        } catch (\Exception $e) {
        }
    }


}
