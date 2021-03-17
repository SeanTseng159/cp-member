<?php


namespace App\Services\Ticket;
use App\Services\BaseService;

use App\Repositories\Ticket\MemberCouponOnlineRepository;



class MemberCouponOnlineService extends BaseService
{
    protected $repository;

    public function __construct(MemberCouponOnlineRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listCanUsed($memberID)
    {
        return $this->repository->listCanUsed($memberID);
    }
}