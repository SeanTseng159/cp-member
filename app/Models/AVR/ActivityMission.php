<?php

namespace App\Models\AVR;

class ActivityMission extends AVRBaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'avr_activity_missions';

   
    public function missions()
    {
        return $this->hasMany(Mission::class,'id','mission_id');
    }
    public function activity()
    {
        return $this->belongsTo(Activity::class,'activity_id','id');
    }


}
