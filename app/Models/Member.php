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
    
    public function gifts($type)
    {
        if ($type == 1)
        {
            return $this->scopeUsableGifts();
        }
        else
        {
            return $this->scopeUnavailableGifts();
        }
    }
    
    public function memberGiftItem()
    {
        
        
        return $this->hasMany(MemberGiftItem::class)->toSql();
        
    }
    public function memberGift()
    {

        dd($this->hasManyThrough(Gift::class,MemberGiftItem::class,'gift_id','id','c','gift_id')->toSql());
        return $this->hasManyThrough(Gift::class,MemberGiftItem::class,'gift_id','id','c','gift_id');
        
    }
    
    
    public function scopeUsableGifts()
    {
        return $this->memberGift();
//        return $this->memberGiftItem()
//            ->whereHas('memberGiftItem.gift',
//                function ($query) {
//                    $query
//                        ->where('start_at', '<=', Carbon::now())
//                        ->where('expire_at', '>=', Carbon::now())
//                        ->whereNull('used_time');
//                })
//            ->with('memberGiftItem.gift')
//            ->whereHas('gift.diningCar')
//            ->with('gift.diningCar');
        
    }
    
    
    public function scopeUnavailableGifts()
    {
        return $this
            ->hasMany(
                MemberGiftItem::class,
                'member_id',
                'id')
            ->whereHas('gift',
                function ($query) {
                    $query
                        ->where('start_at', '<', Carbon::now())
                        ->orWhere('expire_at', '>', Carbon::now())
                        ->whereNotNull('used_time');
                })
            ->with('gift');
    }
}
