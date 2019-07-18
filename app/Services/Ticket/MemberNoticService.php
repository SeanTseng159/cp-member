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

}
