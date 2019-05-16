<?php


namespace App\Http\Controllers\Api\V1;


use App\Models\AVR\Activity;
use App\Result\AVRActivityResult;
use App\Services\AVR\ActivityService;
use App\Services\AVR\MissionService;
use App\Services\Ticket\OrderService;
use App\Traits\MemberHelper;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class AVRActivityController extends RestLaravelController
{
    use MemberHelper;

    protected $activityService;
    protected $missionService;
    protected $orderDetailService;

    public function __construct(ActivityService $service, MissionService $missionService,OrderService $orderDetailService)
    {
        $this->activityService = $service;
        $this->missionService = $missionService;
        $this->orderDetailService = $orderDetailService;

    }


    public function list(Request $request)
    {

        try {
            $memberID = $this->getMemberId();
            $data = $this->activityService->list($memberID);
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


    public function missionList(Request $request, $activityId, $orderId)
    {

        try {

            if (!$activityId) {
                throw new \Exception('E0001');
            }

            $memberID = $this->getMemberId();

            if (!$memberID && $orderId != 0) {
                throw new \Exception('E0001');
            }
            if ($orderId == 0) $orderId = null;

            $data = $this->activityService->detail($activityId, $orderId);

            //檢查訂單編號
            if ($data->has_prod_spec_price_id && $data->prod_spec_price_id && !$orderId)
                throw new \Exception('E0080');

            if (!$data)
                return $this->success();


            $data = (new AVRActivityResult)->missionList($data, $memberID, $orderId);

            return $this->success($data);
        } catch (\Exception $e) {
            $code = $e->getMessage() ? $e->getMessage() : 'E0001';
            return $this->failureCode($code);
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
            $code = $e->getMessage() ? $e->getMessage() : 'E0001';
            return $this->failureCode($code);
        }
    }

    public function missionEnd(Request $request, $orderId, $missionId)
    {
        try {
            $memberID = $request->memberId;
            $point = $request->point;

            if (is_null($point)) {
                throw  new \Exception('E0001');
            }

            if ($orderId == 0) $orderId = null;

            $mission = $this->missionService->detail($missionId);

            if(!$mission){
                throw  new \Exception('E0001');
            }

            $activityID = $mission->activity_id;
            $activity = Activity::find($activityID);


            //檢查訂單編號
            if ($activity->has_prod_spec_price_id && $activity->prod_spec_price_id && !$orderId)
                throw new \Exception('E0080');

            //發送禮物與寫入DB
            $ret = $this->missionService->end($activityID, $mission->id, $mission->name, $memberID, $mission->passing_grade, $point, $orderId);

            //成功就核銷票卷
            if( isset($ret->mission) && $ret->mission->complete)
            {
                if($activity->prod_spec_price_id)
                {
                    $this->orderDetailService->activityObliterate($orderId);
                }
            }
            return $this->success($ret);

        } catch (\Exception $e) {
            \Log::error($e);
            $code = $e->getMessage() ? $e->getMessage() : 'E0001';
            return $this->failureCode($code);
        }

    }

    public function cancelMission(Request $request, $orderId, $missionId)
    {
        try {
            $memberID = $request->memberId;

            if (is_null($missionId)) {
                throw  new \Exception('E0001');
            }
            if ($orderId == 0) $orderId = null;
            $ret = $this->missionService->delete($missionId, $memberID, $orderId);
            return $this->success();

        } catch (\Exception $e) {
            $code = $e->getMessage() ? $e->getMessage() : 'E0001';
            return $this->failureCode($code);
        }
    }


}
