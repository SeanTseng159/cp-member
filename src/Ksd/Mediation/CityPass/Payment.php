<?php
/**
 * Created by PhpStorm.
 * User: jofo
 * Date: 2018/1/16
 * Time: 上午 11:24
 */

namespace Ksd\Mediation\CityPass;


class Payment extends Client
{
    public function tspgATMReturn($result)
    {
        if (!empty($result) && count($result) > 0) {
            try {
                $response = $this->putParameters($result)
                    ->request('POST', 'payment_tspg/parse_atm_writeoff');

                $body = $response->getBody();
                $data = json_decode($body, true);
                if ($data['statusCode'] == 200) {
                    return true;
                }
            } catch (\Exception $exception) {}
            return false;
        }
        return true;
    }
}