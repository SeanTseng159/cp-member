<?php


namespace App\Http\Controllers\Api\V1;


use App\Enum\TimeStatus;
use App\Models\AVR\Activity;
use App\Result\AVRActivityResult;
use App\Services\AVR\ActivityService;
use App\Services\AVR\MissionService;
use App\Services\Ticket\OrderDetailService;
use App\Traits\MemberHelper;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;


class AVRActivityController extends RestLaravelController
{
    use MemberHelper;

    protected $activityService;
    protected $missionService;
    protected $orderDetailService;

    public function __construct(ActivityService $service, MissionService $missionService,
                                OrderDetailService $orderDetailService)
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
            \Log::error($e);
            return $this->failureCode('E0001');
        }
    }


    /**
     * 任務清單
     * @param Request $request
     * @param $activityId
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     */

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

            $activity = $this->checkOrderId($activityId, $orderId);

            if (!$activity)
                return $this->success();


            $activityStatus = TimeStatus::checkStatus($activity->start_activity_time, $activity->end_activity_time);
            $data = (new AVRActivityResult)->missionList($activity, $activityStatus, $memberID, $orderId);

            return $this->success($data);
        } catch (\Exception $e) {
            $code = $e->getMessage() ? $e->getMessage() : 'E0001';
            return $this->failureCode($code);
        }
    }

    public function missionDetail(Request $request, $orderId, $missionId)
    {

        try {

            $memberID = $this->getMemberId();

            if (!$memberID && $orderId != 0) {
                throw new \Exception('E0080');
            }

            if ($orderId == 0) $orderId = null;


            $mission = $this->missionService->detail($missionId, $memberID, $orderId);

            $activityStatus = TimeStatus::checkStatus($mission->activity->start_activity_time, $mission->activity->end_activity_time);

            $data = (new AVRActivityResult)->missionDetail($activityStatus, $mission, $memberID, $orderId);
            return $this->success($data);

        } catch (\Exception $e) {
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

            //檢查orderID是否屬於member
            if ($orderId) {
                $orderRecord = $this->orderDetailService->find($orderId);
                if (is_null($orderRecord)) {
                    throw  new \Exception('E0081');
                }
                if ($orderRecord->order_detail_member_id != $memberID) {
                    throw  new \Exception('E0081');
                }
            }


            $mission = $this->missionService->detail($missionId, $memberID, $orderId);


            if (!$mission) {
                throw  new \Exception('E0001');
            }

            $activity = $mission->activity;
            //檢查訂單編號
            if ($activity->has_prod_spec_price_id && $activity->prod_spec_price_id && !$orderId)
                throw new \Exception('E0080');


            //發送禮物與寫入DB，也核銷票卷
            $ret = $this->missionService->end($activity->id, $mission->id, $mission->name, $memberID, $mission->passing_grade, $point, $orderId);

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

            //檢查orderID是否屬於member
            if ($orderId) {
                $orderRecord = $this->orderDetailService->find($orderId);
                if (is_null($orderRecord)) {
                    throw  new \Exception('E0081');
                }
                if ($orderRecord->order_detail_member_id != $memberID) {
                    throw  new \Exception('E0081');
                }
            }
            $ret = $this->missionService->delete($missionId, $memberID, $orderId);
            return $this->success();

        } catch (\Exception $e) {
            $code = $e->getMessage() ? $e->getMessage() : 'E0001';
            return $this->failureCode($code);
        }
    }

    /**
     * @param $activityId
     * @param $orderId
     * @return mixed
     * @throws \Exception
     */
    private function checkOrderId($activityId, $orderId)
    {
        $data = $this->activityService->detail($activityId, $orderId);
        if (is_null($data)) {
            throw new \Exception('E0001');
        }

        //檢查訂單編號
        if ($data->has_prod_spec_price_id && $data->prod_spec_price_id && !$orderId)
            throw new \Exception('E0080');
        return $data;
    }


}
