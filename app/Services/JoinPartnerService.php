<?php
/**
 * User: lee
 * Date: 2019/05/09
 * Time: 上午 9:42
 */

namespace App\Services;

use App\Repositories\JoinPartnerRepository;

class JoinPartnerService
{

    protected $repository;

    public function __construct(JoinPartnerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 新增
     * @param $data
     * @return Collection
     */
    public function create($data = [])
    {
        return $this->repository->create($data);
    }
}
