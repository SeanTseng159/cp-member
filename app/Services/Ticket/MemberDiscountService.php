<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

                            

use App\Services\BaseService;

use App\Repositories\Ticket\MemberDiscountRepository;



class MemberDiscountService extends BaseService
{
    protected $repository;

    public function __construct(MemberDiscountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listCanUsed($memberID)
    {
        return $this->repository->listCanUsed($memberID);
    }



}
