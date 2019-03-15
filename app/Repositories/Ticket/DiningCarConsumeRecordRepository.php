<?php

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\DiningCarConsumeRecord;

class DiningCarConsumeRecordRepository extends BaseRepository
{
    /**
     * Default model.
     *
     * @var string
     */
    protected $model;

    public function __construct(DiningCarConsumeRecord $model)
    {
        $this->model = $model;
    }
}
