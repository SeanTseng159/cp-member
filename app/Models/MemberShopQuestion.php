<?php

namespace App\Models;

use App\Models\Ticket\Supplier;
use Illuminate\Database\Eloquent\Model;

class MemberShopQuestion extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'dining_car_question_member';
    protected $connection = 'backend';
    protected $fillable = ['question_detail_id', 'member_id', 'value','sonsumption'];



}
