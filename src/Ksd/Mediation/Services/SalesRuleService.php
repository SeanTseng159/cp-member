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

    public function addCoupon($parameters)
    {
        $salesRule = $this->repository->addCoupon($parameters);
        return [
            'name' => $salesRule->name,
        ];
    }

    public function deleteCoupon($parameters)
    {
        $this->repository->deleteCoupon($parameters);
    }
}