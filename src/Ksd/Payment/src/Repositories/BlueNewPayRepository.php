<?php
/**
 * User: Lee
 * Date: 2018/07/10
 * Time: 下午2:20
 */

namespace Ksd\Payment\Repositories;


use Ksd\Payment\Models\Client;
use Exception;
use Log;


class BlueNewPayRepository extends Client
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * merchantValidation
     * @param $parameters
     * @return mixed
     */
    public function merchantValidation($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'v1/bluenewpay/merchant');

            $result = json_decode($response->getBody(), true);

            return $result;

        } catch (Exception $e) {
            Log::debug('=== 藍新金流 error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        }
    }

}
