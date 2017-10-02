<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 3:08
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Helper\MemberHelper;
use Ksd\Mediation\Magento\Checkout as MagentoCheckout;

class CheckoutRepository extends BaseRepository
{
    use MemberHelper;

    public function __construct()
    {
        $this->magento = new MagentoCheckout();
        parent::__construct();
    }

    /**
     * 取得結帳資訊
     * @param $source
     * @return array
     */
    public function info($source)
    {
        if($source === ProjectConfig::MAGENTO) {
            return $this->magento->authorization($this->userToken())->info();
        } else if ($source === ProjectConfig::CITY_PASS) {

        }
        return [];
    }

    /**
     * 確定結帳
     * @param $parameters
     */
    public function confirm($parameters)
    {
        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            $this->magento->authorization($this->userToken())->confirm($parameters);
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {

        }

    }
}