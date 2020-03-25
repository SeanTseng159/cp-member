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
use Ksd\Mediation\CityPass\SalesRule as CityPassSalesRule;
use Ksd\Mediation\Services\MemberTokenService;

class SalesRuleRepository extends BaseRepository
{
    use EnvHelper;

    private $memberTokenService;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoSalesRule();
        $this->cityPass = new CityPassSalesRule();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
    }

    /**
     * 使用折扣優惠
     * @param $parameters
     * @return bool
     */
    public function addCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            if($this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->addCoupon($parameters->code)) {
                return $this->magento->authorization($this->env('MAGENTO_ADMIN_TOKEN'))->couponDetail($parameters->code);
            }
        } else if ($parameters->source === ProjectConfig::CITY_PASS or $parameters->source === ProjectConfig::CITY_PASS_PHYSICAL) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->addCoupon($parameters);

        }
    }

    /**
     * 取消折扣優惠
     * @param $parameters
     * @return bool
     */
    public function deleteCoupon($parameters)
    {
        if($parameters->source === ProjectConfig::MAGENTO) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->deleteCoupon();
        } else if ($parameters->source === ProjectConfig::CITY_PASS or $parameters->source === ProjectConfig::CITY_PASS_PHYSICAL) {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->deleteCoupon($parameters);
        }
    }
}