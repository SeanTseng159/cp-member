<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class FaqCategory extends BaseModel
{
	protected $primaryKey = 'faq_category_id';

	/**
     * 取得內容
     */
  	public function contents()
    {
        return $this->hasMany('App\Models\Ticket\FaqContent', 'faq_category_id');
	}
}
