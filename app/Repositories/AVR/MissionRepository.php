<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\AVR;


use App\Config\Ticket\TicketConfig;
use App\Enum\ActivityType;
use App\Enum\BarCodeType;
use App\Models\AVR\ActivityAward;
use App\Models\Ticket\OrderDetail;
use App\Services\UUID;
use App\Models\AVR\Activity;
use App\Models\AVR\MemberMission;
use App\Models\AVR\Mission;
use App\Models\Award;
use App\Models\AwardRecord;
use App\Repositories\BaseRepository;
use Carbon\Carbon;



class MissionRepository extends BaseRepository
{
    protected $model;
    protected $memberMissionModel;
    protected $activityModel;
    private $orderDetail;

    public function __construct(Activity $activityModel, Mission $model, MemberMission $memberMission,
                                OrderDetail $orderDetail)
    {

        $this->model = $model;
        $this->memberMissionModel = $memberMission;
        $this->activityModel = $activityModel;
        $this->orderDetail = $orderDetail;
    }

    public function detail($id, $memberId = null, $orderId = null)
    {

        if (!$orderId) {
            $data = $this->model->with('activity')->where('id', $id)->first();
        } else {

            $data = $this->model
                ->with(['activity',
                    'activity.productPriceId',
                    'activity.productPriceId.orderDetail' => function ($query) use ($memberId, $orderId) {
                        $query->where('order_detail_id', $orderId)->where('member_id', $memberId);
                    }])
                ->where('id', $id)
                ->first();
        }

        return $data;

    }

    public function delete($missionID, $memberID, $orderId = null)
    {

        return $this->memberMissionModel
            ->where('mission_id', $missionID)
            ->where('member_id', $memberID)
            ->where('order_detail_id', $orderId)
            ->update(
                [
                    'point' => 0,
                    'isComplete' => 0
                ]
            );
    }

    /**
     * 任務結束，也更新產品狀態和核銷票卷
     * @param $activityID
     * @param $missionID
     * @param $missionName
     * @param $memberID
     * @param $passPoint
     * @param $userPoint
     * @param null $orderId
     * @return array|\stdClass
     */

    public function end($activityID, $missionID, $missionName, $memberID, $passPoint, $userPoint, $orderId = null)
    {

        $missionStatus = $this->memberMissionModel
            ->firstOrNew([
                'member_id' => $memberID,
                'mission_id' => $missionID,
                'order_detail_id' => $orderId
            ]);


        $isComplete = false;
        if ($userPoint >= $passPoint)
            $isComplete = true;

        //已完成任務
        if ($missionStatus && $missionStatus->isComplete) {
            return [];
        }


        //回傳資料
        $ret = new \stdClass;

        $ret->mission = new \stdClass();
        $ret->mission->name = $missionName;
        $ret->mission->complete = $isComplete;
        $award = null;
        $activityAward = null;

        //取得任務禮物
        if ($isComplete) {
            //檢查mission是否有禮物
            $award = $this->getMissionAward($missionID);
            if ($award) {
                $ret->mission->award = new \stdClass();
                $ret->mission->award->name = $award->award_name;
                $ret->mission->award->photo = $award->image->img_path;
            }

            //檢查活動是否完成
            $activityMissionStatus = $this->activityModel->with([
                'missions',
                'missions.members' => function ($query) use ($memberID, $orderId) {
                    $query->where('member_id', $memberID)->where('order_detail_id', $orderId);
                }])
                ->where('id', $activityID)
                ->first();

            $missions = $activityMissionStatus->missions;
            $activityName = $activityMissionStatus->name;

            $activityComplete = true;
            foreach ($missions as $mission) {
                //不存在此會員
                if (count($mission->members) == 0) {
                    $activityComplete = false;
                    break;
                }

                //除了這筆之外的任務都已經完成
                if (!$mission->members[0]->isComplete && $mission->id != $missionID) {
                    $activityComplete = false;
                    break;
                }
            }

            $ret->activity = new \stdClass();
            $ret->activity->complete = $activityComplete;
            $ret->activity->name = $activityName;

            //如果完成，確認是否有禮物
            if ($activityComplete) {
                $activityAward = $this->getActivityAward($activityID);
                if ($activityAward) {
                    $ret->activity->award = new \stdClass();
                    $ret->activity->award->name = $activityAward->award_name;
                    $ret->activity->award->photo = $activityAward->image->img_path;
                }
            }
        }

        //寫入DB
        //會員-任務狀態
        \DB::connection('avr')->transaction(function () use (
            $activityID,
            $missionStatus,
            $memberID,
            $missionID,
            $userPoint,
            $isComplete,
            $orderId,
            $award,
            $activityAward
        ) {
            try {
                if (!$missionStatus) {
                    $missionStatus = $this->memberMissionModel->create([
                        'member_id' => $memberID,
                        'mission_id' => $missionID,
                        'point' => $userPoint,
                        'isComplete' => $isComplete,
                        'order_detail_id' => $orderId
                    ]);
                } else {
                    $missionStatus->isComplete = $isComplete;
                    $missionStatus->point = $userPoint;
                    $missionStatus->order_detail_id = $orderId;
                }
                $missionStatus->save();

                //award/award_record/order_detail DB 更新
                \DB::connection('backend')->transaction(function () use (
                    $award, $activityAward, $memberID, $activityID, $missionID, $orderId
                ) {
                    try {
                        if ($award) {
                            //獎品紀錄
                            $award->award_used_quantity = $award->award_used_quantity + 1;
                            $award->modified_at = Carbon::now();
                            $award->save();
                            //獲獎紀錄
                            $awardRecord = new AwardRecord;
                            $awardRecord->award_id = $award->award_id;
                            $awardRecord->user_id = $memberID;
                            $awardRecord->activity_id = $activityID;
                            $awardRecord->model_name = Mission::class;
                            $awardRecord->model_type = ActivityType::avr_mission;
                            $awardRecord->model_spec_id = $missionID;

                            $awardRecord->qrcode = (new UUID())->setCreate()->getToString();
                            $awardRecord->supplier_id = $award->supplier_id;
                            $awardRecord->barcode = '';
                            $awardRecord->barcode_type = BarCodeType::code_39;
                            $awardRecord->verifier_id = 0;
                            $awardRecord->created_at = Carbon::now();
                            $awardRecord->modified_at = Carbon::now();
                            $awardRecord->save();
                        }
                        if ($activityAward) {
                            $activityAward->award_used_quantity = $activityAward->award_used_quantity + 1;
                            $activityAward->modified_at = Carbon::now();
                            $activityAward->save();


                            $awardRecord = new AwardRecord;
                            $awardRecord->award_id = $activityAward->award_id;
                            $awardRecord->user_id = $memberID;
                            $awardRecord->activity_id = $activityID;
                            $awardRecord->model_name = Activity::class;
                            $awardRecord->model_type = ActivityType::avr_activity;
                            $awardRecord->model_spec_id = $activityID;
                            $awardRecord->qrcode = (new UUID())->setCreate()->getToString();
                            $awardRecord->supplier_id = $activityAward->supplier_id;
                            $awardRecord->barcode = '';
                            $awardRecord->barcode_type = BarCodeType::code_39;
                            $awardRecord->verifier_id = 0;
                            $awardRecord->created_at = Carbon::now();
                            $awardRecord->modified_at = Carbon::now();
                            $awardRecord->save();
                        }
                        //訂單表
                        $orderDetail = $this->orderDetail->find($orderId);
                        if ($orderDetail) {
                            if (is_null($orderDetail->verified_at)) {
                                $orderDetail->verified_at = Carbon::now();
                                $orderDetail->verifier_id = 1;
                                $orderDetail->verified_status = TicketConfig::DB_STATUS[1];
                                $orderDetail->save();
                            }
                        }

                    } catch (\Exception $e) {
                        throw new \Exception($e);
                    }
                });


            } catch (\Exception $e) {
                throw new \Exception($e);
            }

        });


        return $ret;


    }


    /*
     * 根據機率取得禮物資料
     */
    private function getMissionAward($missionId)
    {
        $mission = $this->model
            ->with('missionAwards')
            ->where('id', $missionId)
            ->first();

        if (!$mission)
            return false;

        $missionAwards = $mission->missionAwards;
        if ($missionAwards->count() <= 0)
            return null;


        $probabilityList = $missionAwards->pluck('probability')->toArray();

        $award = $this->getAwardByProbability($probabilityList);
        $awardID = $missionAwards[$award]->award_id;
        $award = Award::with('image')->where('award_id', $awardID)->first();

        if (
            $award &&
            $award->award_stock_quantity - $award->award_used_quantity > 0 &&
            $award->award_budget_cancellation_status == false &&
            $award->award_status == true &&
            Carbon::now() >= $award->award_validity_start_at &&
            Carbon::now() < $award->award_validity_end_at) {

            return $award;
        }
        return null;


    }

    private function getActivityAward($activityID)
    {
        $activityAwards =
            ActivityAward::where('activity_id', $activityID)
                ->with('awards')
                ->get();

        if (count($activityAwards) == 0) return null;

        $probabilityList = $activityAwards->pluck('probability')->toArray();

        $awardIndex = $this->getAwardByProbability($probabilityList);
        $awardID = $activityAwards[$awardIndex]->award_id;
        $award = Award::with('image')->where('award_id', $awardID)->first();


        if (
            $award &&
            $award->award_stock_quantity - $award->award_used_quantity > 0 &&
            $award->award_budget_cancellation_status == false &&
            $award->award_status == true &&
            Carbon::now() >= $award->award_validity_start_at &&
            Carbon::now() < $award->award_validity_end_at) {

            return $award;
        }

        return null;

    }

    /**
     * 根據亂數產生的結果，取得他在機率陣列內的index
     * @param $probabilities
     * @return int
     */
    private function getAwardByProbability($probabilities): int
    {

        $probabilityList = [];
        $sum = 0;
        foreach ($probabilities as $probability) {

            $sum += $probability;
            $probabilityList[] = $sum;
        }

        $random = rand(1, $sum);
        $index = 0;

        for ($i = 0; $i < count($probabilityList); $i++) {
            $next = $i + 1;

            if ($random >= $probabilityList[$i] and $random < $probabilityList[$next]) {
                $index = $next;
                break;
            }
        }

        return $index;
    }


}
