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
        $this->name = $this->getPaymentMethod($this->arrayDefault($result, 'title'));
        $this->type =  $this->paymentType($this->arrayDefault($result, 'code'));
    }


    /**
     * 設定付款方式type對應
     * @param $key
     * @return string
     */
    public function paymentType($key)
    {
        switch ($key) {
            case 'neweb_transmit': # 信用卡一次付清
                return 'credit_card';
            case 'neweb_atm': # 虛擬帳號一次付清
                return 'atm';
            case 'ipasspay': # ipasspay
                return 'ipass_pay';
            case 'checkmo': # check
                return 'credit_card';
            case 'tspg_transmit': # check
                return 'credit_card';
            case 'tspg_atm': # check
                return 'atm';
        }
    }

    /**
     * 付款方式name名稱轉換
     * @return string
     */
    public function getPaymentMethod($key)
    {
        switch ($key) {
            case 'Check / Money order':
                return "測試用";
            case 'Neweb Api Payment':
                return "(藍新)信用卡一次付清";
            case 'Neweb Atm Payment':
                return "(藍新)ATM虛擬帳號";
            case 'Ipass Pay':
                return "Ipass Pay";
            case 'Tspg Api Payment':
                return "(台新)信用卡一次付清";
            case 'Tspg Atm Payment':
                return "(台新)ATM虛擬帳號";
        }

    }

}