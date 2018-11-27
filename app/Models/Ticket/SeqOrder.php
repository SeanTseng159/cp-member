<?php
/**
 * User: lee
 * Date: 2018/11/23
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class SeqOrder extends BaseModel
{
	protected $primaryKey = 'seq_order_id';

	public $timestamps = false;
}
