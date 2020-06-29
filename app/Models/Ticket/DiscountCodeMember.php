<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;

class DiscountCodeMember extends BaseModel
{
    

    protected $table = 'discount_code_members';
    
    protected $primaryKey = 'discount_code_member_id';
    protected $connection = 'backend';
    
    public function discountCode()
    {
        return $this->hasOne('App\Models\Ticket\DiscountCode', 'discount_code_id', 'discount_code_id');
    }
}
