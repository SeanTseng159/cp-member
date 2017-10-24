<?php
/**
 * Created by PhpStorm.
 * User: ching
 * Date: 2017/10/24
 * Time: 上午 09:42
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification  extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
}