<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;

class DiscountCode extends BaseModel
{
    use BackendSoftDeletes;

    protected $table = 'discount_codes';

    protected $primaryKey = 'discount_code_id';

    public function discountCodeTags()
    {
        return $this->hasMany(DiscountCodeTag::class, 'discount_code_tag_id', 'discount_code_tag_id');
    }

}
