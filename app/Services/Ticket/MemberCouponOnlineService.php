<?php


namespace App\Services\Ticket;
use App\Services\BaseService;

use App\Repositories\Ticket\MemberCouponOnlineRepository;
use App\Repositories\Ticket\MemberCouponRepository;



class MemberCouponOnlineService extends BaseService
{
    protected $repository;

    public function __construct(MemberCouponRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listCanUsed($memberID)
    {
        return $this->repository->listCanUsed($memberID);
    }
}