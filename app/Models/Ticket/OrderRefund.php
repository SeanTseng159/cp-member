<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class OrderRefund extends BaseModel
{
    protected $table = 'order_refunds';
    protected $primaryKey = 'order_refund_id';
}
