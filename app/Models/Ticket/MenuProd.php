<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class MenuProd extends BaseModel
{
    protected $table = 'menu_prods';
    protected $primaryKey = 'menu_prod_id';

    /**
     * 取得標籤
     */
  	public function upperTag()
    {
        return $this->hasOne('App\Models\Ticket\Tag', 'tag_id', 'tag_upper_id')->where('tag_status', 1);
    }

    /**
     * 取得標籤
     */
  	public function tag()
    {
        return $this->hasOne('App\Models\Ticket\Tag', 'tag_id', 'tag_id')->where('tag_status', 1);
    }
}
