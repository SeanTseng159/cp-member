<?php

namespace App\Models\AVR;

class ActivityMission extends AVRBaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'avr_activity_missions';

    public function memberMissions()
    {
        return $this->hasMany(MemberMission::class,'mission_id','mission_id');
    }


}
