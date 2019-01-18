<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class ProductSpecPrice extends BaseModel
{
    protected $table = 'prod_spec_prices';
    protected $primaryKey = 'prod_spec_price_id';

    public $timestamps = false;
}
