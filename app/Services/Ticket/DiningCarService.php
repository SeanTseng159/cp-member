<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\DiningCarRepository;

class DiningCarService extends BaseService
{
    protected $repository;

    public function __construct(DiningCarRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取列表
     * @return mixed
     */
    public function list()
    {
        return $this->repository->list();
    }
}
