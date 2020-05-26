<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class ProductSpec extends BaseModel
{
    protected $table = 'prod_specs';
    protected $primaryKey = 'prod_spec_id';

    /**
     * 取得規格票種
     */
  	public function specPrices()
    {
        return $this->hasMany('App\Models\Ticket\ProductSpecPrice', 'prod_spec_id')->notDeleted();
    }

    /**
     * 取得產品
     */
    public function product()
    {
        return $this->hasOne('App\Models\Ticket\Product', 'prod_id', 'prod_id')->onSale();
    }

    /**
     * 取得產品,尚未下架
     */
    public function productOnShelf()
    {
        return $this->hasOne('App\Models\Ticket\Product', 'prod_id', 'prod_id')->onShelf();
    }


    /**
     * 取得所有產品
     */
    public function productAll()
    {
        return $this->hasOne('App\Models\Ticket\Product', 'prod_id', 'prod_id');
    }
}
