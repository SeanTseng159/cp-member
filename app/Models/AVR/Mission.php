<?php

namespace App\Models\AVR;

class Mission extends AVRBaseModel
{
    protected $primaryKey = 'id';

    public function members()
    {
        return $this->hasMany(MemberMission::class);
    }
}
