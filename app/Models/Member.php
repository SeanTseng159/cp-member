<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Models;

use Carbon\Carbon;
//use Illuminate\Database\Eloquent\Model;
use Hoyvoy\CrossDatabase\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];
    
    protected $appends = ['showPwd'];
    
    protected $connection = 'mysql';
    protected $table = 'members';
    
    /**
     * 加入是否顯示修改密碼
     */
    public function getShowPwdAttribute()
    {
        return ($this->openPlateform === 'citypass');
    }
}
