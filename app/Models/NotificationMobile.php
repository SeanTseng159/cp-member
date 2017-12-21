<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/18
 * Time: 上午 10:12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationMobile extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

}