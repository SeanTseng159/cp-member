<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;



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
     *
     */
    public function list()
    {
        return $this->repository->list();
    }



}
