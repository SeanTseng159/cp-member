<?php
/**
 * User: Lee
 * Date: 2018/11/26
 * Time: 下午2:20
 */

namespace Ksd\Payment\Repositories;

use Ksd\Payment\Models\Client;
use Exception;
use Log;


class TspgRepository extends Client
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate Virtual Account
     * @param $parameters
     * @return mixed
     */
    public function generateVirtualAccount($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'v1/atm/generate');

            $result = json_decode($response->getBody(), true);

            return $result;
        } catch (ClientException $e) {
            Log::debug('=== atm generate virtual account error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== atm generate virtual account unknown error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        }
    }
}
