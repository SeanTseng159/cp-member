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
use Ksd\Mediation\Services\MemberTokenService;

class SalesRuleRepository extends BaseRepository
{
    use EnvHelper;

    private $memberTokenService;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoSalesRule();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
    }

    public function addCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            if($this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->addCoupon($parameters->code)) {
                return $this->magento->authorization($this->env('MAGENTO_ADMIN_TOKEN'))->couponDetail($parameters->code);
            }
        } else if ($parameters->source === ProjectConfig::CITY_PASS) {

        }
    }

    public function deleteCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->deleteCoupon($parameters->code);
        } else if ($parameters->source === ProjectConfig::CITY_PASS) {

        }
    }
}