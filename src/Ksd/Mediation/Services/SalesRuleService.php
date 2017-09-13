<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/12
 * Time: 下午 5:28
 */

namespace Ksd\Mediation\Services;


use Ksd\Mediation\Helper\MemberHelper;
use Ksd\Mediation\Repositories\SalesRuleRepository;

class SalesRuleService
{
    use MemberHelper;

    private $repository;

    public function __construct()
    {
        $this->repository = new SalesRuleRepository();
    }

    public function addCoupon($parameters)
    {
        $salesRule = $this->repository->setToken($this->userToken())->addCoupon($parameters);
        return [
            'name' => $salesRule->name,
        ];
    }

    public function deleteCoupon($parameters)
    {
        $this->repository->deleteCoupon($parameters);
    }
}