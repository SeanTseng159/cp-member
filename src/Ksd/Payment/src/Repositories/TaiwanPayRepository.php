<?php
/**
 * User: Lee
 * Date: 2018/07/10
 * Time: 下午2:20
 */

namespace Ksd\Payment\Repositories;

use Ksd\Mediation\CityPass\Checkout as CityPassCheckout;
use Ksd\Payment\Models\Client;
use Exception;
use Log;


class TaiwanPayRepository extends Client
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * reserve
     * @param $parameters
     * @return mixed
     */
    public function saveTransacctions($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'v1/taiwanpay/saveTransacctions');

            $result = json_decode($response->getBody(), true);

            return $result;
        } catch (ClientException $e) {
            Log::debug('=== TaiwanPay reserve error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== TaiwanPay reserve unknown error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        }
    }

}
