<?php
/**
 * User: lee
 * Date: 2019/01/30
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Menu extends BaseModel
{
    /**
     * 取得所有分類
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Ticket\MenuCategory', 'menu_category_id');
    }

	/**
     * 取得封面圖
     */
    public function mainImg()
    {
        return $this->hasOne('App\Models\Ticket\MenuImg')->where('sort', 1);
    }

    /**
     * 取得封面圖
     */
    public function imgs()
    {
        return $this->hasMany('App\Models\Ticket\MenuImg');
    }
}
