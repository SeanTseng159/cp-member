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

    public function detail($id)
    {
        return $this->repository->detail($id);
    }

    public function end($activityID,$missionID,$missionName,$memberID,$passPoint,$point)
    {
        return $this->repository->end($activityID,$missionID,$missionName,$memberID,$passPoint,$point);
    }

    public function delete($missionID, $memberID)
    {
        return $this->repository->delete($missionID, $memberID);
    }


}
