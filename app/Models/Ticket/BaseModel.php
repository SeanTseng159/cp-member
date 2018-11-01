<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Awobaz\Compoships\Compoships;

class BaseModel extends Model
{
	use Compoships;

	protected $connection = 'backend';

	/**
     * 未被刪除的
     */
	public function scopeNotDeleted($query)
    {
    	return $query->where('deleted_at', 0);
    }
}
