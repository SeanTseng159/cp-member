<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Helpers\ClientType;
use App\Models\Ticket\BaseModel;
use App\Models\Ticket\DiningCar;

class Gift extends BaseModel
{
    protected $table = 'gifts';
    protected $clientType = ['dining_car'];
    protected $connection = 'backend';
    
    public function __construct()
    {
    
    }
    
    public function diningCar()
    {
        return $this->hasOne(DiningCar::class, 'id', 'model_spec_id');
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
    
    /*
     * 取得禮物券
     *
     * @return \Awobaz\Compoships\Database\Eloquent\Relations\HasMany
     */
    public function memberGiftItems()
    {
        return $this->hasMany('App\Models\Ticket\MemberGiftItem');
        
    }
    
    
}
