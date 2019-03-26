<?php

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\DiningCarConsumeRecordRepository;

class DiningCarConsumeRecordService extends BaseService
{
    /**
     * Default repository.
     *
     * @var string
     */
    protected $repository;

    public function __construct(DiningCarConsumeRecordRepository $repository)
    {
        $this->repository = $repository;
    }
}
