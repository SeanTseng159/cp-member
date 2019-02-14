<?php
/**
 * User: Annie
 * Date: 2019/02/13
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use Illuminate\Pagination\Paginator;
use App\Models\Ticket\DiningCar;

class MemberCouponRepository extends BaseRepository
{
    private $limit = 20;
    
}
