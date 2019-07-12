<?php
/**
 * User: Danny
 * Date: 2019/07/11
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\PromoteRepository;

class InvitationService extends BaseService
{
    protected $repository;

    public function __construct(PromoteRepository $promoteRepository)
    {
        $this->promoteRepository = $promoteRepository;
    }

    public function allPromoteGift()
    {
        return $this->promoteRepository->allPromoteGift();
    }

    public function addRecord($gifts = null, $id = null,$passiveMemberId = null)
    {
        return $this->promoteRepository->addRecord($gifts, $id,$passiveMemberId);
    }
}
