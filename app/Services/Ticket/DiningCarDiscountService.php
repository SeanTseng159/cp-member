<?php
/**
 * Created by Fish.
 * 2019/12/19 5:44 下午
 */

namespace App\Services\Ticket;

use App\Repositories\DiningCarDiscountRepositories;
use App\Services\BaseService;


class DiningCarDiscountService extends BaseService
{
    protected $repository;

    public function __construct(DiningCarDiscountRepositories $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function checkCount($id)
    {
        return $this->repository->checkCount($id);    
    }
}