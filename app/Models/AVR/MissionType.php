<?php

namespace App\Models\AVR;

class MissionType extends AVRBaseModel
{
    protected $primaryKey = 'id';


    public function contents()
    {
        return $this->hasMany(MissionTypeContent::class);
    }


}
