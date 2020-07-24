<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class GreenPoint extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'green_point';
    protected $connection = 'backend';
    protected $fillable = ['member_id','used'];



}
