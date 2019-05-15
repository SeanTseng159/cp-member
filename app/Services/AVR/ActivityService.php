<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\AVR;


use App\Repositories\Ticket\ActivityRepository;
use App\Services\BaseService;


class ActivityService extends BaseService
{
    protected $repository;


    /**
     * ActivityService constructor.
     * @param ActivityRepository $repository
     */
    public function __construct(ActivityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param null $memberID
     * @return |null
     */
    public function list($memberID = null)
    {
        return $this->repository->list($memberID);
    }

    public function detail($id,$orderId = null)
    {
        return $this->repository->detail($id,$orderId);
    }


}
