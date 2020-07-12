<?php
/**
 * User: lee
 * Date: 2020/07/12
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;


class GuestOrder extends BaseModel
{
    protected $connection = 'backend';

    public function __construct()
    {

    }
}
