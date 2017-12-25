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
use Illuminate\Support\Facades\DB;

class Notification  extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    public function getAfterDate($date){

        $notifications = DB::table('notifications')
                            ->select('*')
                            ->where('updated_at', '>=', $date)
                            ->get();

        return $notifications;

    }
}