<?php

namespace App\Models\AVR;

class Mission extends AVRBaseModel
{
    protected $primaryKey = 'id';

    public function members()
    {
        return $this->hasMany(MemberMission::class,'mission_id','id');
    }
    public function contents()
    {
        return $this->hasMany(MissionTypeContent::class);
    }
    public function missionAwards()
    {
        return $this->hasMany(MissionAward::class)->orderBy('probability');
    }
    public function activity()
    {
        return $this->belongsTo(Activity::class,'activity_id','id');
    }

}
