<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TspgPostbackRecord extends Model
{
    protected $guarded = ['id'];
    public $table = "linepay_feedback_record";
}
