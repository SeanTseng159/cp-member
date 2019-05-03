<?php

namespace App\Models\AVR;

class MemberMission extends AVRBaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'member_missions';
    public $timestamps = true;
    protected $fillable = ['member_id', 'mission_id', 'point','isComplete'];

}
