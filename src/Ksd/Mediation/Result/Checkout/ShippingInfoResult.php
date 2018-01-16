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

    /**
     * magento 配送資料建置
     * @param $result
     */
    public function magento($result)
    {
        $this->source = ProjectConfig::MAGENTO;
        $this->id = sprintf('%s_%s', $this->arrayDefault($result, 'method_code'), $this->arrayDefault($result, 'carrier_code'));
        $this->name = $this->arrayDefault($result, 'carrier_title');
        $this->description = $this->arrayDefault($result, 'carrier_title');
        $this->type = $this->getStatus(sprintf('%s_%s', $this->arrayDefault($result, 'method_code'), $this->arrayDefault($result, 'carrier_code')));
    }

    /**
     * magento物流狀態代碼轉換
     * @param $key
     * @return string
     */
    public function getStatus($key)
    {
        if(isset($key)) {
            if($key === 'flatrate_flatrate'){
                return "delivery";
            }else{
                return "delivery";
            }
        }else{
            return "delivery";
        }

    }
}