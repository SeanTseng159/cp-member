<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;

class DiscountCodeMember extends BaseModel
{
    

    protected $table = 'discount_code_members';
    
    protected $primaryKey = 'discount_code_member_id';
    protected $connection = 'backend';
    // use BackendSoftDeletes;
}
