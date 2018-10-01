<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class ProductKeyword extends BaseModel
{
    protected $table = 'prod_keywords';
    protected $primaryKey = 'prod_keyword_id';

    /**
     * 取得關鍵字
     */
  	public function keyword()
    {
        return $this->belongsTo('App\Models\Ticket\Keyword', 'keyword_id');
	}

	/**
     * 取得商品
     */
  	public function prdouct()
    {
        return $this->belongsTo('App\Models\Ticket\Product', 'prod_id');
	}
}
