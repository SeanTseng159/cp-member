<?php
/**
 * User: Annie
 * Date: 2019/02/14
 * Time: 上午 11:55
 */

namespace App\Result;


use App\Enum\AVRImageType;
use App\Enum\MissionFileType;
use App\Helpers\AVRImageHelper;
use Carbon\Carbon;
use App\Traits\StringHelper;

class AVRActivityResult extends BaseResult
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
            $result->photo = AVRImageHelper::getImageUrl(AVRImageType::activity, $activity->id);

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
        $result->photo = AVRImageHelper::getImageUrl(AVRImageType::activity, $activity->id);

        $result->description = $activity->introduction;

        return $result;

    }

    public function missionList($activity, $memberID)
    {

        $result = new \stdClass;

        $result->mission = [];
        $missions = $activity->missions;

        $finishNum = 0;

        foreach ($missions as $mission) {
            $ret = new \stdClass();
            $ret->id = $mission->id;
            $ret->Name = $mission->name;
            $ret->longitude = $mission->longitude;
            $ret->latitude = $mission->latitude;
            $user = $mission->members->where('member_id', $memberID)->first();

            if (!$user) {
                $ret->status = false;
            } else
                $ret->status = (bool)$user->isComplete;
            $ret->photo = AVRImageHelper::getImageUrl(AVRImageType::mission, $mission->id);
            $result->mission[] = $ret;

            if ($ret->status)
                $finishNum++;
        }

        $result->allNum = count($missions);
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
        $ret->photo = AVRImageHelper::getImageUrl(AVRImageType::mission, $mission->id);

        //使用者相關
        $user = $mission->members->where('member_id', $memberID)->first();
        if (!$user)
            $ret->status = false;
        else
            $ret->status = (bool)$user->isComplete;

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
                } else {
                    $obj->detail = $item->content;
                }


                $content[] = $obj;
            }
            $game->content = $content;


            $ret->game = $game;
        }


        return $ret;

    }


}
