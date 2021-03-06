<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\AVR;


use App\Repositories\AVR\MissionRepository;
use App\Services\BaseService;


class MissionService extends BaseService
{
    protected $repository;

    public function __construct(MissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function detail($id, $memberID = null, $orderId = null)
    {
        return $this->repository->detail($id, $memberID, $orderId);
    }

    public function end($activityID, $missionID, $missionName, $memberID, $passPoint, $point, $orderId = null)
    {
        return $this->repository->end($activityID, $missionID, $missionName, $memberID, $passPoint, $point, $orderId);
    }

    public function delete($missionID, $memberID, $orderId = null)
    {
        return $this->repository->delete($missionID, $memberID, $orderId);
    }


}
