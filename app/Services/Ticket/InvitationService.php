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

    public function addRecord($gifts = null, $MemberId = null,$passiveMemberId = null)
    {
        return $this->promoteRepository->addRecord($gifts, $MemberId,$passiveMemberId);
    }

    public function friendValue($memberId)
    {
        return $this->promoteRepository->friendValue($memberId);
    }

    public function invitationCheck($passiveMemberId)
    {
        return $this->promoteRepository->invitationCheck($passiveMemberId);
    }

    public function findPromoteGift()
    {
        return $this->promoteRepository->findPromoteGift();
    }

    public function list($type, $memberId, $client, $clientId)
    {
        return $this->promoteRepository->list($type, $memberId, $client, $clientId);
    }

    public function findPromoteGiftRecord($id, $memberId)
    {
        return $this->promoteRepository->findPromoteGiftRecord($id, $memberId);
    }

    public function availablePromoteGift($memberId)
    {
        return $this->promoteRepository->availablePromoteGift($memberId);
    }
}
