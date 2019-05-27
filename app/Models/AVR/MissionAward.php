<?php

namespace App\Models\AVR;

use App\Models\Award;

class MissionAward extends AVRBaseModel
{
    protected $primaryKey = 'id';

    protected $table = 'mission_awards';


    public function award()
    {
        return $this->hasOne(Award::class,'award_id','award_id');
    }



}
