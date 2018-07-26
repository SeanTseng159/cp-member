<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class LayoutExploration extends BaseModel
{
	protected $primaryKey = 'layout_exploration_id';

	/**
     * 取得tag
     */
	public function tag()
	{
	    return $this->belongsTo('App\Models\Ticket\Tag', 'layout_exploration_tag_id');
	}
}
