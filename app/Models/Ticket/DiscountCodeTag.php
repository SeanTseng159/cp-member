<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;

class DiscountCodeTag extends BaseModel
{
    use BackendSoftDeletes;

    protected $table = 'discount_code_tags';

    protected $primaryKey = 'discount_code_tag_id';


}
