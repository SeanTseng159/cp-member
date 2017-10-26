<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2017/10/18
 * Time: 上午 10:42
 */

namespace Ksd\Mediation\Magento;


class Customer extends Client
{
    public function token($member)
    {
        $name = $member->name;
        if (mb_strlen($name, 'UTF-8') > 1) {
            $firstName = mb_substr($name, 0, 1, 'UTF-8');
            $lastName = mb_substr($name, 1, mb_strlen($name, 'UTF-8') , 'UTF-8');
        } else {
            $firstName = $name;
            $lastName = $name;
        }
        $customer = new \stdClass();
        $customer->email = $member->email;
        $customer->firstname = $firstName;
        $customer->lastname = $lastName;

        $this->putParameters([
            'customer' => $customer,
            'password' => mb_substr($member->password, 0, 8)
        ]);
        $response = $this->request('POST', 'V1/ksd/customer/token');
        $body = $response->getBody();

        return trim($body, '"');
    }

}