<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class Keyword extends BaseModel
{
	protected $table = 'keywords';
    protected $primaryKey = 'keyword_id';
}
