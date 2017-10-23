<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 2:20
 */

namespace Ksd\Mediation\Result\Checkout;


use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Helper\ObjectHelper;

class PaymentInfoResult
{
    use ObjectHelper;

    /**
     * magento 付款資料建置
     * @param $result
     */
    public function magento($result)
    {
        $this->source = ProjectConfig::MAGENTO;
        $this->id = $this->arrayDefault($result, 'code');
        $this->name = $this->arrayDefault($result, 'title');
        $this->type =  $this->paymentType($this->arrayDefault($result, 'code'));
    }


    /**
     * 設定付款方式
     * @param $payment
     * @return string
     */
    public function paymentType($key)
    {
        switch ($key) {

            case 'neweb_transmit': # 信用卡一次付清
                return 'credit_card';
                break;
            case 'neweb_atm': # 虛擬帳號一次付清
                return 'atm';
                break;

        }
    }



}