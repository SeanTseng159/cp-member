<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result;


use App\Enum\WaitingStatus;

class ShopWaitingResult
{

    public function info($waiting)
    {
        $result = new \stdClass;

        //沒有候位清單
        if (!$waiting->waitingList) {
            $result->currentNo = 0;
            $result->WaitingNum = 0;
            $result->capacity = optional($waiting->waitingSetting)->capacity;
            return $result;
        }


        $oncallRecords = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::OnCall;
        });

        $WaitingRecords = $waiting->waitingList->filter(function ($item) {
            return $item->status == WaitingStatus::Waiting;
        });
        $currentNo = count($oncallRecords) > 0 ? ($oncallRecords->first())->waiting_no : 0;
        $WaitingNum = count($WaitingRecords);

        $result->currentNo = $currentNo;
        $result->WaitingNum = $WaitingNum;
        $result->capacity = $waiting->waitingSetting->capacity;
        return $result;
    }

    /**任務的狀態需要活動狀態一致  若活動未開始或已過期，都為不可執行，除非任務已完成
     * @param $activity
     * @param $activityStatus
     * @param null $memberID
     * @param null $orderId
     * @return \stdClass
     */
    public function missionList($activity, $activityStatus, $memberID = null, $orderId = null)
    {

        $result = new \stdClass;

        $result->mission = [];
        $missions = $activity->missions;
        $hasOrder = $activity->is_mission; //必須依照排序


        $finishNum = 0;

        foreach ($missions as $mission) {
            static $preMission;
            $ret = new \stdClass();
            $ret->id = $mission->id;
            $ret->name = $mission->name;
            $ret->longitude = $mission->longitude;
            $ret->latitude = $mission->latitude;

            $user = $mission->members
                ->where('member_id', $memberID)
                ->where('order_detail_id', $orderId)
                ->first();


            //status 1:不可執行 2:可執行 3:已完成
            $status = 1;


            if ($user) {
                $status = $user->isComplete ? 3 : 2;
            } else {
                //按照順序執行的任務，前一個任務已完成，才能執行
                if ($hasOrder) {
                    if (!$preMission) {
                        $status = 2;
                    } else if ($preMission->status == 3) {
                        $status = 2;
                    }
                } else {
                    $status = 2;
                }
            }

            //如果活動已過期或未開始，則任務皆為不可執行，除非任務已經完成
            if ($activityStatus != TimeStatus::PROCESSING) {
                if ($status != 3)
                    $status = 1;
            }


            $ret->status = $status;
            $ret->photo = AVRImageHelper::getImageUrl(AVRImageType::avr_mission, $mission->id);
            $result->mission[] = $ret;

            $preMission = $mission; //上一個
            $preMission->status = $status;

            if ($ret->status == 3)
                $finishNum++;
        }

        $result->allNum = count($missions);
        $result->finishNum = $finishNum;

        return $result;

    }

    public function missionDetail($activityStatus, $mission, $memberID = null, $orderId = null)
    {

        $ret = new \stdClass();
        $ret->id = $mission->id;
        $ret->name = $mission->name;
        $ret->description = $mission->introduction;
        $ret->place = $mission->place_name;
        $ret->longitude = $mission->longitude;
        $ret->latitude = $mission->latitude;
        $ret->checkGps = (bool)$mission->check_gps;
        $ret->photo = AVRImageHelper::getImageUrl(AVRImageType::avr_mission, $mission->id);

        //使用者相關
        $user = $mission->members->where('member_id', $memberID)->where('order_detail_id', $orderId)->first();

        //status 1:不可執行 2:可執行 3:已完成
        $status = 1;

        if ($user) {
            $status = $user->isComplete ? 3 : 2;
        } else {
            $status = $activityStatus;
        }
        $ret->status = $status;


        $game = new \stdClass();
        $game->type = $mission->type;
        $game->time = $mission->game_length;
        $game->pass = $mission->passing_grade;


        //遊戲相關
        $gameContent = $mission->contents;

        if ($gameContent) {
            $content = [];
            foreach ($gameContent as $item) {
                $obj = new \stdClass();
                $obj->target = $item->usage_type;
                $obj->type = $item->content_type;

                if ($item->content_type == MissionFileType::recognition_id) {
                    $obj->detail = $item->recognition->name;
                } else if ($item->content_type == MissionFileType::url)
                    $obj->detail = $item->content;
                else
                    $obj->detail = CommonHelper::getAdHost($item->content);

                if ($item->content_type != MissionFileType::color) {
                    $content[] = $obj;
                }
                //顏色放在game的property裡面
                if ($item->content_type == MissionFileType::color) {
                    $game->color = $item->content;
                }


            }
            $game->content = $content;


            $ret->game = $game;
        }


        return $ret;

    }


}
