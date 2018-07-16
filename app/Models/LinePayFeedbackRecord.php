<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinePayFeedbackRecord extends Model
{
    protected $guarded = ['id'];
    public $table = "linepay_feedback_record";
}
