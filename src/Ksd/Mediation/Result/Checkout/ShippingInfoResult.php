<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 2:21
 */

namespace Ksd\Mediation\Result\Checkout;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Helper\ObjectHelper;

class ShippingInfoResult
{
    use ObjectHelper;

    public function magento($result)
    {
        $this->source = ProjectConfig::MAGENTO;
        $this->id = sprintf('%s_%s', $this->arrayDefault($result, 'method_code'), $this->arrayDefault($result, 'carrier_code'));
        $this->name = $this->arrayDefault($result, 'method_title');
        $this->description = $this->arrayDefault($result, 'carrier_title');
        $this->asSameBuyer = false;
    }
}