<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class ShopQuestionDetail extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'dining_car_question_detail';
    protected $connection = 'backend';
    protected $fillable = ['question_id', 'order', 'type','min','man','options','title'];



}
