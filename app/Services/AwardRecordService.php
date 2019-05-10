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

    public function list($type,$memberId)
    {
        return $this->repository->list($type,$memberId);
    }
}
