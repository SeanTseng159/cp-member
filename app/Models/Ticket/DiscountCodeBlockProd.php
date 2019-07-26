<?php

namespace App\Models\Ticket;

use App\Traits\BackendSoftDeletes;

class DiscountCodeBlockProd extends BaseModel
{

    protected $table = 'discount_code_block_prods';

    protected $primaryKey = 'discount_code_block_prod_id';


}
