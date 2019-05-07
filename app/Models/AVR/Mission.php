<?php

namespace App\Models\AVR;

class Mission extends AVRBaseModel
{
    protected $primaryKey = 'id';

    public function members()
    {
        return $this->hasMany(MemberMission::class);
    }
    public function typeData()
    {
        return $this->hasOne(MissionType::class);
    }
    public function missionAwards()
    {
        return $this->hasMany(MissionAward::class)->orderBy('probability');
    }
    public function activityMission(){

        return $this->belongsTo(ActivityMission::class,'id','mission_id');
    }
}
