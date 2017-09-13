<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/12
 * Time: 下午 5:26
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Helper\EnvHelper;
use Ksd\Mediation\Magento\SalesRule as MagentoSalesRule;

class SalesRuleRepository extends BaseRepository
{
    use EnvHelper;

    public function __construct()
    {
        $this->magento = new MagentoSalesRule();
        parent::__construct();
    }

    public function addCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            if($this->magento->authorization($this->token)->addCoupon($parameters->code)) {
                return $this->magento->authorization($this->env('MAGENTO_ADMIN_TOKEN'))->couponDetail($parameters->code);
            }
        } else if ($parameters->source === ProjectConfig::TPASS) {

        }
    }

    public function deleteCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            $this->magento->authorization($this->token)->deleteCoupon($parameters->code);
        } else if ($parameters->source === ProjectConfig::TPASS) {

        }
    }
}