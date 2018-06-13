<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class ProductAdditional extends BaseModel
{
    protected $table = 'prod_additionals';
    protected $primaryKey = 'prod_additional_id';
}
