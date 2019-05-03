<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\AVR;


use App\Models\AVR\MemberMission;
use App\Models\AVR\Mission;
use App\Models\Member;
use App\Repositories\BaseRepository;


class MissionRepository extends BaseRepository
{
    private $limit = 20;
    protected $model;
    protected $memberMissionModel;

    public function __construct(Mission $model, MemberMission $memberMission)
    {

        $this->model = $model;
        $this->memberMissionModel = $memberMission;
    }

    public function detail($id)
    {
        $data = $this->model->with('typeData')->where('id', $id)->first();

        return $data;

    }

    public function end($missionID, $memberID, $passPoint, $userPoint)
    {

        $mishionStatus = $this->memberMissionModel
            ->firstOrNew([
                'member_id' => $memberID,
                'mission_id' => $missionID
            ]);


        $isComplete = false;
        if ($userPoint >= $passPoint)
            $isComplete = true;

        if (!$mishionStatus) {
            $ret = $this->memberMissionModel->create([
                'member_id' => $memberID,
                'mission_id' => $missionID,
                'point' => $userPoint,
                'isComplete' => $isComplete
            ]);
            
        } else {
            $mishionStatus->isComplete = $isComplete;
            $mishionStatus->point = $userPoint;
            $ret = $mishionStatus->save();
        }
        return $ret;

    }

}
