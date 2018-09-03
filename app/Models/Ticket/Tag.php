<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

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
}
