<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;
use Carbon\Carbon;

class DiscountCodeTag extends BaseModel
{
    // use BackendSoftDeletes;

    protected $table = 'discount_code_tags';

    protected $primaryKey = 'discount_code_tag_id';

    public function tagProdId()
    {
        return $this->hasMany('App\Models\Ticket\TagProd', 'tag_id', 'tag_id');
    }

    

}
