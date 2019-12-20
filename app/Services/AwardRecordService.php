<?php


namespace App\Services;

use App\Repositories\AwardRecordRepository;


class AwardRecordService extends BaseService
{
    protected $repository;

    public function __construct(AwardRecordRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function list($type, $memberId, $client = null, $clientId = null)
    {
        return $this->repository->list($type, $memberId, $client, $clientId);
    }

    public function find($id, $memberId)
    {
        return $this->repository->find($id, $memberId);
    }

    public function availableAward($memberId)
    {
        return $this->repository->availableAward($memberId);
    }
}
