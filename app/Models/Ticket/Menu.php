<?php
/**
 * User: lee
 * Date: 2019/01/30
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends BaseModel
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * 取得所有分類
     */
    public function category()
    {
        return $this->hasOne('App\Models\Ticket\MenuCategory', 'id', 'menu_category_id');
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

    /**
     * 取得票種
     */
    public function prodSpecPrice()
    {
        return $this->hasOne('App\Models\Ticket\ProductSpecPrice', 'prod_spec_price_id', 'prod_spec_price_id')->notDeleted();
    }

    /**
     * 取得已付款餐車
     */
    public function diningCar()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCar');
    }
}
