<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TspgPostback extends Model
{
    protected $guarded = ['id'];
    public $table = "tspg_postback";
}
