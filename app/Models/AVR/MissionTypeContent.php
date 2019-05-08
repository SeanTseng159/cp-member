<?php

namespace App\Models\AVR;

class MissionTypeContent extends AVRBaseModel
{
    protected $primaryKey = 'id';

    public function recognition()
    {
        return $this->hasOne(MissionTypeContentRecognition::class,'id','content');
    }

}
