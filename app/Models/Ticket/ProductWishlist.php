<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class ProductWishlist extends BaseModel
{
    protected $table = 'prod_wishlists';
    protected $primaryKey = 'prod_wishlist_id';

    /**
     * 取得商品封面圖片
     */
    public function product()
    {
    	$now = date('Y-m-d H:i:s');

        return $this->hasOne('App\Models\Ticket\Product', 'prod_id', 'prod_id')
        			->notDeleted()
        			->where('prod_onshelf', 1)
        			->where('prod_onshelf_time', '<=', $now)
                    ->where('prod_offshelf_time', '>=', $now);
    }

    /**
     * 取得選單商品
     */
    public function menuProds()
    {
        return $this->hasMany('App\Models\Ticket\MenuProd', 'prod_id', 'prod_id')
                    ->notDeleted()
                    ->where('tag_upper_id', '!=', 0);
    }
}
