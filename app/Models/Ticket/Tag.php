<?php

/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use App\Models\Ticket\TagProduct;

class Tag extends BaseModel
{
    protected $table = 'tags';
    protected $primaryKey = 'tag_id';

    /**
     * 取得標籤
     */
    public function subMenus()
    {
        return $this->hasMany('App\Models\Ticket\Tag', 'tag_upper_id', 'tag_id');
    }

    /**
     * 取得相關產品
     */
    public function products()
    {
        return $this->hasMany('App\Models\Ticket\TagProduct', 'tag_id', 'tag_id');
    }
}
