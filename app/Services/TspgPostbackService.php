<?php
/**
 * User: lee
 * Date: 2017/10/06
 * Time: 上午 9:42
 */

namespace App\Services;

use App\Repositories\TspgPostbackRepository;

class TspgPostbackService
{
    protected $repository;

    public function __construct()
    {
        $this->repository = new TspgPostbackRepository;
    }

    /**
     * 依據訂單編號查詢
     * @param $orderNo
     * @return mixed
     */
    public function find($orderNo)
    {
        $member = $this->repository->find($orderNo);

        return $member;
    }
}
