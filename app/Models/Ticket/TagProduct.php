<?php

/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class TagProduct extends BaseModel
{
    protected $table = 'tag_prods';
    protected $primaryKey = 'tag_prod_id';

    /**
     * 取得產品類別
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Ticket\Tag', 'tag_id', 'tag_id');
    }
}
