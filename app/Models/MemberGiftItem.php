<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Models\Ticket\BaseModel;
use App\Models\Ticket\DiningCar;

class MemberGiftItem extends BaseModel
{
    protected $table = 'member_gift_items';
    protected $connection = 'backend';
    
    public function __construct()
    {
    
    }
    
    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }
    
    
    public function diningCar()
    {
        
        return $this->belongsTo(DiningCar::class, 'model_spec_id', 'id');
    }
    
    
}
