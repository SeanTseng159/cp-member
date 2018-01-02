<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/5
 * Time: 上午 9:04
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\ServiceRepository;


class ServiceService
{
    private $repository;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new ServiceRepository($memberTokenService);
    }

    /**
     * 取得常用問題
     * @return array
     */

    public function qa()
    {
        return $this->repository->qa();
    }


    /**
     * 問題與建議
     * @param $parameters
     * @return bool
     */
    public function suggestion($parameters)
    {
        return $this->repository->suggestion($parameters);
    }


}
