<?php
/**
 * User: lee
 * Date: 2018/12/14
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class PromotionProdSpecPrice extends BaseModel
{
    public function proudct()
    {
        return $this->belongsTo(Product::class,'prod_id','prod_id');

    }

}
