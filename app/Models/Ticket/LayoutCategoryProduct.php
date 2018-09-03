<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class LayoutCategoryProduct extends BaseModel
{
	protected $table = 'layout_category_prods';
	protected $primaryKey = 'layout_category_prod_id';
}
