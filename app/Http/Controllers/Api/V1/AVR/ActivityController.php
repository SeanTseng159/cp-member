<?php


namespace App\Http\Controllers\Api\V1\AVR;



use App\Result\AVRActivityResult;
use App\Services\AVR\ActivityService;
use App\Services\AVR\MissionService;
use App\Traits\MemberHelper;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class ActivityController extends RestLaravelController
{
    use MemberHelper;

    protected $activityService;
    protected $missionService;

    public function __construct(ActivityService $service, MissionService $missionService)
    {
        $this->activityService = $service;
        $this->missionService = $missionService;

    }


    public function list(Request $request)
    {

        try {
            $data = $this->activityService->list();
            $data = (new AVRActivityResult)->list($data);
            return $this->success($data);
        } catch (\Exception $e) {
            return $this->failureCode('E0001');
        }
    }

    public function detail(Request $request, $activityId)
    {

        try {

            if (!$activityId) {
                throw new \Exception('E0001');
            }

            $data = $this->activityService->detail($activityId);
            if (!$data)
                return $this->success();
            $data = (new AVRActivityResult)->activityDetail($data);
            return $this->success($data);
        } catch (\Exception $e) {
//            dd($e);
            \Log::error($e);
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
            if (!$data)
                return $this->success();

            $memberID = $this->getMemberId();

            $data = (new AVRActivityResult)->missionList($data, $memberID);

            return $this->success($data);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->failureCode('E0001');
        }
    }

    public function missionDetail(Request $request, $missionId)
    {

        try {
            $memberID = $request->memberId;
            $mission = $this->missionService->detail($missionId);
            $data = (new AVRActivityResult)->missionDetail($mission, $memberID);
            return $this->success($data);

        } catch (\Exception $e) {
//            dd($e);
            \Log::error($e);
            return $this->failureCode('E0001');
        }
    }

    public function missionEnd(Request $request, $missionId)
    {
            try {
                $memberID = $request->memberId;
                $point = $request->point;
                if (is_null($point)) {
                    throw  new \Exception('E0001');
                }

            $mission = $this->missionService->detail($missionId);

            $activityID = $mission->activity_id;

            $ret = $this->missionService->end($activityID, $mission->id, $mission->name, $memberID, $mission->passing_grade, $point);
            return $this->success($ret);

        } catch (\Exception $e) {
//            dd($e);
            \Log::error($e);
            return $this->failureCode('E0001');
        }

    }

    public function cancelMission(Request $request,$missionId)
    {
        try {
            $memberID = $request->memberId;

            if (is_null($missionId)) {
                throw  new \Exception('E0001');
            }

            $ret = $this->missionService->delete($missionId, $memberID);
            return $this->success();

        } catch (\Exception $e) {
            dd($e);
            \Log::error($e);
            return $this->failureCode('E0001');
        }
    }


}
