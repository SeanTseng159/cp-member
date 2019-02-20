<?php
/**
 * User: annie
 * Date: 2019/2/15
 * Time: 下午 02:40
 */


namespace App\Services\Ticket;

use App\Repositories\Ticket\MemberCouponItemRepository;
use App\Repositories\Ticket\MemberCouponRepository;
use App\Services\BaseService;

class MemberCouponItemService extends BaseService
{
    protected $repository;
    
    public function __construct(MemberCouponItemRepository $repository)
    {
        $this->repository = $repository;
    }
    
    
}
