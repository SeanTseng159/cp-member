<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class ProductGroup extends BaseModel
{
    protected $table = 'prod_groups';
    protected $primaryKey = 'prod_group_id';

    public function scopeNotDeleted($query)
    {
    	return $query->where('deleted_at', 0);
    }

    /**
     * 取得商品
     */
  	public function product()
    {
        return $this->belongsTo('App\Models\Ticket\Product', 'prod_group_prod_id');
	}
}
