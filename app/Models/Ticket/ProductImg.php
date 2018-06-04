<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class ProductImg extends BaseModel
{
    protected $table = 'prod_imgs';
    protected $primaryKey = 'prod_img_id';
}
