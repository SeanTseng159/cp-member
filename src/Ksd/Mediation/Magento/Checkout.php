<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/9/7
 * Time: 下午 1:38
 */

namespace Ksd\Mediation\Magento;


use Ksd\Mediation\Helper\MemberHelper;
use Ksd\Mediation\Helper\StringHelper;
use Ksd\Mediation\Result\Checkout\PaymentInfoResult;
use Ksd\Mediation\Result\Checkout\ShippingInfoResult;

class Checkout extends BaseClient
{
    use MemberHelper;
    use StringHelper;

    private $cart;

    public function __construct()
    {
        $this->cart = new Cart();
        parent::__construct();
    }

    public function info()
    {
        return [
            'payments' => $this->paymentInfo(),
            'shipments' => $this->shippingInfo(),
            'billings' => $this->billingInfo()
        ];
    }

    public function paymentInfo()
    {
        $response = $this->request('GET', 'V1/carts/mine/payment-information');
        $result = json_decode($response->getBody(), true);
        $data = [];
        if(array_key_exists('payment_methods', $result))
            foreach ($result['payment_methods'] as $row) {
                $paymentInfo = new PaymentInfoResult();
                $paymentInfo->magento($row);
                $data[] = $paymentInfo;
            }

        return $data;

    }

    public function shippingInfo()
    {
        $response = $this->request('GET', 'V1/carts/mine/shipping-methods');
        $result = json_decode($response->getBody(), true);
        $data = [];
        foreach ($result as $row) {
            $shippingInfo = new ShippingInfoResult();
            $shippingInfo->magento($row);
            $data[] = $shippingInfo;
        }
        return $data;
    }

    public function billingInfo()
    {
        return [];
    }

    public function confirm($parameters)
    {
        $this->putShipping($parameters->shipment());
        $this->putPayment($parameters->payment());
    }

    public function putShipping($shipment)
    {
        $address = $this->processAddress($shipment);
        $methods = mb_split('_', $shipment->id);

        $this->putParameters([
            'addressInformation' => [
                'shipping_address' => $address,
                'billing_address' => $address,
                'shipping_method_code' => $methods[0],
                'shipping_carrier_code' => $methods[1],
            ],
            'shipping_method_code' => $methods[0],
            'shipping_carrier_code' => $methods[1],
        ]);

        $this->request('POST', 'V1/carts/mine/shipping-information');
    }

    public function putPayment($payment)
    {
        $parameter = [
            'paymentMethod' => [
                'method' => $payment->id,
                'additional_data' => [
                    'cc_type' => $this->creditCardType($payment->creditCardNumer),
                    'cc_exp_year' => $payment->creditCardYear,
                    'cc_exp_month' => $payment->creditCardMonth,
                    'cc_number' => $payment->creditCardNumer,
                    'cc_cid' => $payment->creditCardcCode,
                ]
            ]
        ];
        $this->putParameters($parameter);
        $this->request('POST', 'V1/carts/mine/payment-information');
    }

    private function processAddress($shipment)
    {
        $userNames = $this->str_split_unicode($shipment->userName);
        if(count($userNames) < 2) {
            // TODO:實作收件人名字不足兩個字處理
        }
        $firstName = array_shift($userNames);
        $lastName = join('', $userNames);
        return [
            'region' => $shipment->userAddress->area,
            'region_code' => $shipment->userAddress->area,
            'country_id' => 'TW',
            'street' => [
                $shipment->userAddress->street
            ],
            'telephone' => $shipment->userPhone,
            'postcode' => $shipment->userPostalCode,
            'city' => $shipment->userAddress->city,
            'firstname' => $firstName,
            'lastname' => $lastName
        ];
    }

    private function creditCardType($number)
    {
        $length = strlen($number);
        if (substr($number,0, 1) === '4' &&  ($length === 13 || $length === 16 || $length === 19 )) {
            return 'VI';
        } else if((
            (intval(substr($number,0, 2)) >= 51 && intval(substr($number,0, 2)) <= 55) ||
            (intval(substr($number,0, 4)) >= 2221 && intval(substr($number,0, 4)) <= 2720)
            ) && $length === 16) {
            return 'MC';
        } else if(
            (intval(substr($number,0, 4)) >= 3528 && intval(substr($number,0, 4)) <= 3589)
            && $length === 16 ) {
            return 'JCB';
        }
    }
}