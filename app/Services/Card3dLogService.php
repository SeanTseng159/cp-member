<?php

namespace App\Services;

use App\Repositories\Card3dLogRepository;

class Card3dLogService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new Card3dLogRepository;
    }

    /**
     * 新增Log
     * @param $data
     * @return \App\Models\Card3dErrorLog
     */
    public function create($data = [])
    {
        $log = $this->repository->create($data);

        return $log;
    }
}
