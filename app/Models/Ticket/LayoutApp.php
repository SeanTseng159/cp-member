<?php
/**
 * User: lee
 * Date: 2018/10/02
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class LayoutApp extends BaseModel
{
	use SoftDeletes;

	protected $dates = ['deleted_at'];
}
