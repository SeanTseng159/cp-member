<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\AVR;


use App\Config\Ticket\TicketConfig;
use App\Enum\AVRImageType;
use App\Enum\AwardRecordType;
use App\Enum\BarCodeType;
use App\Helpers\CommonHelper;
use App\Models\AVR\ActivityAward;
use App\Models\PayReceive;
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
                        $query->where('order_detail_id', $orderId)->where('order_detail_member_id', $memberId);
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
        $ret->mission->complete = $isComplete;
        $ret->mission->name = $missionName;


        $missionAward = null;
        $activityAward = null;
        $activityComplete = false;


        list($activityName, $activityComplete) =
            $this->checkIsActivityFinish($activityID, $missionID, $memberID, $orderId, $isComplete);

        $ret->activity = new \stdClass();
        $ret->activity->complete = $activityComplete;
        $ret->activity->name = $activityName;


        //檢查mission是否有禮物
        if ($isComplete) {
            list($missionAward, $photo, $allOut) = $this->getMissionAward($missionID);

            $ret->mission->award = new \stdClass();
            $ret->mission->award->allOut = $allOut;
            if ($missionAward) {
                $ret->mission->award->name = $missionAward->award_name;
                $ret->mission->award->photo = $photo;
            }

        } else {
            //失敗就回傳
            return $ret;
        }

        //DB Transaction
        //會員-任務狀態
        \DB::connection('avr')->transaction(function () use (
            $activityID, $missionStatus, $memberID, $missionID,
            $userPoint, $isComplete, $orderId, $missionAward, $activityComplete,
            $ret, $allOut
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
                    $missionAward, $memberID, $activityID, $missionID, $orderId,
                    $activityComplete, $ret, $allOut
                ) {
                    try {
                        if ($missionAward && !$allOut) {
                            $missionAward->award_used_quantity = $missionAward->award_used_quantity + 1;
                            $missionAward->modified_at = Carbon::now();
                            $missionAward->save();
                            //獲獎紀錄
                            $awardRecord = new AwardRecord;
                            $awardRecord->award_id = $missionAward->award_id;
                            $awardRecord->user_id = $memberID;
                            $awardRecord->activity_id = $activityID;
                            $awardRecord->model_name = Mission::class;
                            $awardRecord->model_type = AwardRecordType::avr_mission;
                            $awardRecord->model_spec_id = $missionID;

                            $awardRecord->qrcode = (new UUID())->setCreate()->getToString();
                            $awardRecord->supplier_id = $missionAward->supplier_id;
                            $awardRecord->barcode = '';
                            $awardRecord->barcode_type = BarCodeType::code_39;
                            $awardRecord->verifier_id = 0;
                            $awardRecord->created_at = Carbon::now();
                            $awardRecord->modified_at = Carbon::now();
                            $awardRecord->save();
                        }
                        //如果活動完成，確認是否有禮物
                        if ($activityComplete) {
                            list($activityAward, $photo, $activityAllOut) = $this->getActivityAward($activityID);

                            $ret->activity->award = new \stdClass();
                            $ret->activity->award->allOut = $activityAllOut;
                            if ($activityAward) {
                                $ret->activity->award->name = $activityAward->award_name;
                                $ret->activity->award->photo = $photo;
                                if (!$activityAllOut) {
                                    $activityAward->award_used_quantity = $activityAward->award_used_quantity + 1;
                                    $activityAward->modified_at = Carbon::now();
                                    $activityAward->save();

                                    $awardRecord = new AwardRecord;
                                    $awardRecord->award_id = $activityAward->award_id;
                                    $awardRecord->user_id = $memberID;
                                    $awardRecord->activity_id = $activityID;
                                    $awardRecord->model_name = Activity::class;
                                    $awardRecord->model_type = AwardRecordType::avr_activity;
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
                            }
                        }
                        //訂單表
                        $orderDetail = $this->orderDetail->find($orderId);
                        if ($orderDetail && is_null($orderDetail->verified_at)) {
                            $orderDetail->verified_at = Carbon::now();
                            $orderDetail->verifier_id = 1;
                            $orderDetail->verified_status = TicketConfig::DB_STATUS[1];
                            $orderDetail->save();
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

        $missionAward = null;
        $awardPhoto = null;
        $allOut = false;

        $mission = $this->model
            ->with(['missionAwards', 'missionAwards.award'])
            ->where('id', $missionId)
            ->first();

        if (!$mission) {
            return [$missionAward, $awardPhoto, $allOut];
        }


        $missionAwards = $mission->missionAwards;

        if ($missionAwards->count() <= 0) {
            return [$missionAward, $awardPhoto, $allOut];
        }


        $probabilityList = [];

        //取得還有數量的禮物做比例分配
        foreach ($missionAwards as $item) {
            $award = $item->award;
            if ($award->award_stock_quantity - $award->award_used_quantity > 0) {
                $probabilityList[$award->award_id] = $item->probability;
            }
        }

        if (!count($probabilityList)) {
            $allOut = true;
            return [$missionAward, $awardPhoto, $allOut];
        }


        $awardID = $this->getAwardByProbability($probabilityList);
        $missionAward = Award::with('image')->where('award_id', $awardID)->first();


        if ($missionAward &&
            $missionAward->award_status == true &&
            Carbon::now() >= $missionAward->award_validity_start_at &&
            Carbon::now() < $missionAward->award_validity_end_at) {

            $awardPhoto = CommonHelper::getBackendHost($missionAward->image->img_path);

            if ($missionAward->award_stock_quantity - $missionAward->award_used_quantity > 0) {
                $allOut = false;
            } else {
                $allOut = true;
            }

        }
        return [$missionAward, $awardPhoto, $allOut];
    }

    private function getActivityAward($activityID)
    {
        $activityAward = null;
        $photo = '';
        $allOut = false;

        $activityAwards =
            ActivityAward::where('activity_id', $activityID)
                ->with('award')
                ->get();

        if (count($activityAwards) == 0) {
            return [$activityAward, $photo, $allOut];
        }


        $probabilityList = [];

        //取得還有數量的禮物做比例分配
        foreach ($activityAwards as $item) {
            $award = $item->award;
            if ($award->award_stock_quantity - $award->award_used_quantity > 0 && $item->probability > 0) {
                $probabilityList[$award->award_id] = $item->probability;
            }
        }

        if (count($probabilityList) <= 0) {
            return [$activityAward, $photo, $allOut];
        }

        $awardIndex = $this->getAwardByProbability($probabilityList);

        $award = Award::with('image')->where('award_id', $awardIndex)->first();
        if ($award &&
            $award->award_status == true &&
            Carbon::now() >= $award->award_validity_start_at &&
            Carbon::now() < $award->award_validity_end_at
        ) {
            $photo = CommonHelper::getBackendHost($award->image->img_path);
            if ($award->award_stock_quantity - $award->award_used_quantity > 0) {
                $allOut = false;
            } else {
                $allOut = true;
            }
            return [$award, $photo, $allOut];
        }
        return [$activityAward, $photo, $allOut];
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
        foreach ($probabilities as $index => $probability) {

            $sum += $probability;
            $probabilityList[$index] = $sum;


        }

        $random = rand(1, $sum);
        $index = 0;

        //從大到小比較
        $preserved = array_reverse($probabilityList, true);

        foreach ($preserved as $i => $probability) {
            if ($random <= $probability) {
                $index = $i;
            }
        }
        return $index;
    }

    /** 檢查活動是否完成
     * @param $activityID
     * @param $missionID
     * @param $memberID
     * @param $orderId
     * @param $isComplete
     * @return array
     */
    private function checkIsActivityFinish($activityID, $missionID, $memberID, $orderId, $isComplete): array
    {

        $activityMissionStatus = $this->activityModel->with([
            'missions',
            'missions.members' => function ($query) use ($memberID, $orderId) {
                $query->where('member_id', $memberID)->where('order_detail_id', $orderId);
            }])
            ->where('id', $activityID)
            ->first();

        $missions = $activityMissionStatus->missions;
        $activityName = $activityMissionStatus->name;

        if (!$isComplete)
            return array($activityName, false);

        $activityComplete = true;

        //除了這筆之外的任務都已經完成
        foreach ($missions as $mission) {
            if ($mission->id != $missionID) {
                if ($mission->members->count() == 0) {
                    $activityComplete = false;
                } else if (!$mission->members[0]->isComplete) {
                    $activityComplete = false;
                }
            }
        }
        return array($activityName, $activityComplete);
    }


}
