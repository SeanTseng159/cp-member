<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\AVR;


use App\Enum\ActivityType;
use App\Enum\BarCodeType;
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
    protected $missionModel;
    protected $memberMissionModel;
    protected $activityModel;

    public function __construct(Activity $activityModel, Mission $model, MemberMission $memberMission)
    {

        $this->missionModel = $model;
        $this->memberMissionModel = $memberMission;
        $this->activityModel = $activityModel;
    }

    public function detail($id)
    {
        $data = $this->missionModel->where('id', $id)->first();
        return $data;

    }

    public function end($activityID, $missionID, $memberID, $passPoint, $userPoint)
    {

        $mishionStatus = $this->memberMissionModel
            ->firstOrNew([
                'member_id' => $memberID,
                'mission_id' => $missionID
            ]);


        $isComplete = false;
        if ($userPoint >= $passPoint)
            $isComplete = true;

        if ($mishionStatus && $mishionStatus->isComplete) {
            return [];
        }
        if (!$mishionStatus) {
            $mishionStatus = $this->memberMissionModel->create([
                'member_id' => $memberID,
                'mission_id' => $missionID,
                'point' => $userPoint,
                'isComplete' => $isComplete
            ]);
        } else {
            $mishionStatus->isComplete = $isComplete;
            $mishionStatus->point = $userPoint;
        }

        $ret = new \stdClass;

        \DB::connection('avr')->transaction(function () use (
            $mishionStatus, $isComplete, $missionID, $activityID, $memberID, $ret
        ) {
            try {
                $mishionStatus->save();

                $ret->mission = new \stdClass();
                $ret->mission->complete = $isComplete;

                //取得禮物
                if ($isComplete) {

                    //檢查mission是否有禮物
                    $award = $this->getMissionAward($missionID);

                    if ($award) {
                        $ret->mission->award = new \stdClass();
                        $ret->mission->award->name = $award->award_name;
                        $ret->mission->award->photo = $award->image->img_path;
                    }

                    //檢查activity是否完成
                    $activityMissionStatus = $this->activityModel->with([
                        'missions',
                        'missions.members' => function ($query) use ($memberID) {
                            $query->where('member_id', $memberID);
                        }])
                        ->where('id', $activityID)
                        ->first();
                    $missions = $activityMissionStatus->missions;


                    $activityComplete = true;
                    foreach ($missions as $mission) {
                        if (!$mission->members) {
                            $activityComplete = false;
                            break;
                        }
                        if (!$mission->members[0]->isComplete) {
                            $activityComplete = false;
                            break;
                        }
                    }
                    $ret->activity = new \stdClass();
                    $ret->activity->complete = $activityComplete;

                    \DB::connection('backend')->transaction(function () use (
                        $award, $missionID, $activityID, $memberID, $ret, $activityComplete
                    ) {
                        try {
                            if ($award) {
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

                                //獎品紀錄
                                $award->award_used_quantity = $award->award_used_quantity++;
                                $award->save();
                            }


                            if ($activityComplete) {
                                $activityAward = $this->getActivityAward($activityID);
                                if ($activityAward) {
                                    $ret->activity->award = new \stdClass();
                                    $ret->activity->award->name = $activityAward->award_name;
                                    $ret->activity->award->photo = $activityAward->image->img_path;

                                    //寫入DB
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

                                    $activityAward->award_used_quantity = $activityAward->award_used_quantity++;
                                    $activityAward->save();
                                }
                            }
                        } catch (\Exception $e) {
                            throw new \Exception($e);
                        }
                    });
                }
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
        $mission = $this->missionModel
            ->with('missionAwards')
            ->where('id', $missionId)
            ->first();

        if (!$mission)
            return false;


        $probability = [];
        $sum = 0;

        $missionAwards = $mission->missionAwards;
        if ($missionAwards->count() <= 0)
            return null;

        foreach ($missionAwards as $missionAward) {

            $sum += $missionAward->probability;
            $probability[] = $sum;
        }

        $randon = rand(1, 100);
        $award = 0;

        for ($i = 0; $i < count($probability); $i++) {
            $next = $i + 1;

            if ($randon >= $probability[$i] and $randon < $probability[$next]) {
                $award = $next;
                break;
            }
        }
        $awardID = $missionAwards[$award]->award_id;

        $award = Award::with('image')->where('award_id', $awardID)->first();

        if (
            $award &&
            $award->award_stock_quantity - $award->award_used_quantity > 0 &&
            $award->award_budget_cancellation_status == false &&
            $award->award_status == true &&
            Carbon::now() >= $award->award_launch_start_at &&
            Carbon::now() < $award->award_launch_end_at) {
            return $award;
        }
        return null;


    }

    private function getActivityAward($activityID)
    {
        $activityAward = $this->activityModel
            ->with('award')
            ->where('id', $activityID)
            ->first();

        if (!$activityAward or !$activityAward->award)
            return false;

        $award = Award::with('image')->where('award_id', $activityAward->award->award_id)->first();

        if (
            $award &&
            $award->award_stock_quantity - $award->award_used_quantity > 0 &&
            $award->award_budget_cancellation_status == false &&
            $award->award_status == true &&
            Carbon::now() >= $award->award_launch_start_at &&
            Carbon::now() < $award->award_launch_end_at) {
            return $award;
        }

        return null;

    }


}
