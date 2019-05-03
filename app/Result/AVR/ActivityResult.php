<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result\AVR;

use App\AVR\Helpers\AVRImageHelper;
use App\Enum\AVRClientType;
use App\Enum\ClientType;
use App\Helpers\CommonHelper;
use App\Result\BaseResult;
use Carbon\Carbon;
use App\Traits\StringHelper;
use App\Helpers\ImageHelper;

class ActivityResult extends BaseResult
{
    use StringHelper;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     *
     * @param $activities
     * @return array
     */
    public function list($activities)
    {
        $resultAry = [];


        foreach ($activities as $activity) {
            $result = new \stdClass;
            $result->id = $activity->id;
            $result->name = $activity->name;
            $result->duration = Carbon::parse($activity->start_activity_time)->format('Y-m-d') .
                "~" .
                Carbon::parse($activity->end_activity_time)->format('Y-m-d');

            //圖片
            $result->photo = AVRImageHelper::getImageUrl(AVRClientType::activity, $activity->id);

            $resultAry[] = $result;
        }
        return $resultAry;
    }

    public function activityDetail($activity)
    {
        $result = new \stdClass;
        $result->id = $activity->id;
        $result->name = $activity->name;
        $result->duration = Carbon::parse($activity->start_activity_time)->format('Y-m-d') .
            "~" .
            Carbon::parse($activity->end_activity_time)->format('Y-m-d');

        //圖片
        $result->photo = AVRImageHelper::getImageUrl(AVRClientType::activity, $activity->id);

        $result->description = $activity->introduction;

        return $result;

    }

    public function missionList($activity, $memberID)
    {

        $result = new \stdClass;

        $result->mission = [];
        $activityMissions = $activity->activityMissions;

        $finishNum = 0;

        foreach ($activityMissions as $activityMission) {

            $mission = $activityMission->mission;
            $ret = new \stdClass();
            $ret->id = $mission->id;
            $ret->name = $mission->name;
            $ret->longitude = $mission->longitude;
            $ret->latitude = $mission->latitude;
            $user = $mission->members->where('member_id', $memberID)->first();
            $ret->status = (bool)$user->isComplete;
            $ret->photo = AVRImageHelper::getImageUrl(AVRClientType::mission, $mission->id);
            $result->mission[] = $ret;

            if ($ret->status)
                $finishNum++;
        }

        $result->allNum = count($activity->missions);
        $result->finishNum = $finishNum;

        return $result;

    }

    public function missionDetail($mission, $memberID)
    {


        $ret = new \stdClass();
        $ret->id = $mission->id;
        $ret->name = $mission->name;
        $ret->description = $mission->introduction;
        $ret->place = $mission->place_name;
        $ret->longitude = $mission->longitude;
        $ret->latitude = $mission->latitude;
        $ret->photo = AVRImageHelper::getImageUrl(AVRClientType::mission, $mission->id);

        //使用者相關
        $user = $mission->members->where('member_id', $memberID)->first();
        $ret->status = (bool)$user->isComplete;

        //遊戲相關
        $gameData = $mission->typeData;

        if ($gameData) {
            $game = new \stdClass();
            $game->time = $gameData->game_length;
            $game->pass = $gameData->passing_grade;
            $game->type = $mission->type;

            $gameContent = $gameData->contents;

            if ($gameContent) {
                $content = [];
                foreach ($gameContent as $item) {
                    $obj = new \stdClass();
                    $obj->target = $item->usage_type;
                    $obj->type = $item->content_type;
                    $obj->detail = $item->content;
                    $content[] = $obj;
                }
                $game->content = $content;

            }
            $ret->game = $game;
        }




        return $ret;

    }


}
