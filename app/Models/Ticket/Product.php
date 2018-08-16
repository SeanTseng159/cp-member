<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Product extends BaseModel
{
	protected $table = 'prods';
	protected $primaryKey = 'prod_id';

	protected $appends = ['source'];

	/**
     * 加入來源
     */
	public function getSourceAttribute($value)
    {
    	return 'ct_pass';
    }

	/**
     * 取得商品圖片
     */
  	public function imgs()
    {
        return $this->hasMany('App\Models\Ticket\ProductImg', 'prod_id');
	}

    /**
     * 取得商品圖片
     */
    public function img()
    {
        return $this->hasOne('App\Models\Ticket\ProductImg', 'prod_id')->orderBy('img_sort');
    }

    /**
     * 取得商品圖片
     */
    public function specs()
    {
        return $this->hasMany('App\Models\Ticket\ProductSpec', 'prod_id');
    }
}
