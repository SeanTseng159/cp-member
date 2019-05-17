<?php

namespace App\Models\AVR;


use App\Models\Award;

class ActivityAward extends AVRBaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'avr_activity_awards';


    public function awards()
    {
        return $this->hasMany(Award::class,'award_id','award_id');
    }







}
