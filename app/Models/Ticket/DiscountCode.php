<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;
use Carbon\Carbon;
class DiscountCode extends BaseModel
{

    protected $table = 'discount_codes';

    protected $primaryKey = 'discount_code_id';

    public function discountCodeTags()
    {
        return $this->hasMany(DiscountCodeTag::class, 'discount_code_tag_id', 'discount_code_tag_id');
    }
    public function scopeAllow($query)
    {
        return $query->where('discount_code_status', 1)
                    ->where('deleted_at',0)
                    ->where('discount_code_starttime','<=',Carbon::today())
                    ->where('discount_code_endtime','>',Carbon::today());
    }
}
