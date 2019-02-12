<?php
/**
 * User: lee
 * Date: 2019/01/30
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuCategory extends BaseModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * 取得所有菜單
     */
    public function menus()
    {
        return $this->hasMany('App\Models\Ticket\Menu')->where('status', 1)->orderBy('sort', 'asc');
    }
}
