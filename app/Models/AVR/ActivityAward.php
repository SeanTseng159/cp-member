<?php

namespace App\Models\AVR;


use App\Models\Award;

class ActivityAward extends AVRBaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'avr_activity_awards';


    public function award()
    {
        return $this->hasOne(Award::class,'award_id','award_id');
    }







}
