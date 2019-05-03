<?php

namespace App\Models\AVR;

class ActivityMission extends AVRBaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'avr_activity_missions';

   
    public function mission()
    {
        return $this->hasOne(Mission::class,'id','mission_id');
    }


}
