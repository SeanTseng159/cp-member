<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/13
 * Time: 上午 10:02
 */

namespace Ksd\Mediation\Result\SalesRule;


use Ksd\Mediation\Helper\ObjectHelper;

class CouponResult
{
    use ObjectHelper;

    public function magento($result)
    {
        $this->ruleId = $this->arrayDefault($result, 'rule_id');
        $this->code = $this->arrayDefault($result, 'code');
        $this->timesUsed = $this->arrayDefault($result, 'times_used');
    }
}