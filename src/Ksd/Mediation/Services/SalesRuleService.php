<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/12
 * Time: 下午 5:28
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Repositories\SalesRuleRepository;

class SalesRuleService
{
    private $repository;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->repository = new SalesRuleRepository($memberTokenService);
    }
    /**
     * 使用折扣優惠
     * @param $parameters
     * @return bool
     */
    public function addCoupon($parameters)
    {
        return $this->repository->addCoupon($parameters);

    }

    /**
     * 取消折扣優惠
     * @param $parameters
     * @return bool
     */
    public function deleteCoupon($parameters)
    {
        return $this->repository->deleteCoupon($parameters);
    }
}