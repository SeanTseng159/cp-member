<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use Carbon\Carbon;

class Gift extends BaseModel
{
    public function __construct()
    {
    
    }
    
    /**
     * 取得已啟用的禮物
     *
     * @param Bulider $query
     *
     * @return Bulider
     */
    public function scopeIsActive($query)
    {
        $now = Carbon::now();
        
        return $query->where('status', 1)
            ->where('on_sale_at', '<=', $now)
            ->where('off_sale_at', '>=', $now);
    }
     * 取得禮物券
     *
     * @return \Awobaz\Compoships\Database\Eloquent\Relations\HasMany
     */
    public function memberGiftItems()
    {
        return $this->hasMany('App\Models\Ticket\MemberGiftItem');
        
    }
}
