<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/13
 * Time: 上午 10:02
 */

namespace Ksd\Mediation\Result\SalesRule;


use Ksd\Mediation\Helper\ObjectHelper;

class SalesRuleResult
{
    use ObjectHelper;

    public $coupon;

    public function magento($result)
    {
        $this->name = $this->arrayDefault($result,'name');
    }

    public function setCoupon($coupon)
    {
        $this->coupon = $coupon;
    }
}