<?php
/**
 * User: Danny
 * Date: 2019/07/18
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\MemberNoticRepository;

class MemberNoticService extends BaseService
{
    protected $repository;

    public function __construct(MemberNoticRepository $memberNoticRepository)
    {
        $this->memberNoticRepository = $memberNoticRepository;
    }

    public function memberNoticInfo($params)
    {
        return $this->memberNoticRepository->memberNoticInfo($params);
    }

    public function memberNoticInfoTotal($params)
    {
        return $this->memberNoticRepository->memberNoticInfoTotal($params);
    }

    public function isNotic($memberId = 0, $notificationId = 0)
    {
        $notification = $this->memberNoticRepository->find($memberId, $notificationId);

        return ($notification) ? true : false;
    }
    
    public function updateReadStatus($notificationId)
    {
        return $this->memberNoticRepository->updateReadStatus($notificationId);
    }

    public function availableNotic($memberId)
    {
        return $this->memberNoticRepository->availableNotic($memberId);
    }

}
