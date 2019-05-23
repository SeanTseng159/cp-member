<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class DiningCarSocialUrl extends BaseModel
{
    const SOURCES = ['mobile','line','facebook','instagram'];

    const PAID_SOURCES = ['mobile', 'website', 'line', 'facebook', 'instagram'];
}
