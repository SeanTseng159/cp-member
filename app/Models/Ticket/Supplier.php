<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Supplier extends BaseModel
{
	protected $primaryKey = 'supplier_id';

	public $timestamps = false;

	/**
     * 取得主分類
     */
    public function employee()
    {
        return $this->hasOne('App\Models\Employee', 'supplier_id','supplier_id');
    }
}
