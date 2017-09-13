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

    public function info()
    {
        return $this->magento->authorization($this->userToken())->info();
    }

    public function confirm($parameters)
    {
        if($parameters->checkSource(ProjectConfig::MAGENTO)) {
            $this->magento->authorization($this->userToken())->confirm($parameters);
        } else if ($parameters->checkSource(ProjectConfig::CITY_PASS)) {

        }

    }
}