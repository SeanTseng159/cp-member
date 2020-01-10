<?php
/**
 * User: jerry
 * Date: 2020/01/06
 * Time: 上午 10:03
 */

namespace Ksd\Payment\Repositories;


use Exception;
use Log;
// use  Ksd\Payment\Core\BaseClient;
use  Ksd\Payment\Models\Client;
class YushanPayRepository extends Client
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *public function saveTransacctions($parameters)
     * @param $parameters
     * @return mixed
     */
    public function saveTransacctions($parameters)
    {
        try {
            dd($parameters);
            $response = $this->putParameters($parameters)
                ->request('POST', 'v1/yushanPay/saveTransacctions');

            $result = json_decode($response->getBody(), true);

            return $result;
        } catch (ClientException $e) {
            Log::debug('=== yushanPay saveTransacctions error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== yushanPay saveTransacctions unknown error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        }
    }

    // /**
    //  * reserve
    //  * @param $parameters
    //  * @return mixed
    //  */
    // public function saveTransacctionsReturn($parameters)
    // {
    //     try {
    //         $response = $this->putParameters($parameters)
    //             ->request('POST', 'v1/taiwanpay/saveTransacctionsReturn');

    //         $result = json_decode($response->getBody(), true);

    //         return $result;
    //     } catch (ClientException $e) {
    //         Log::debug('=== TaiwanPay reserve error ===');
    //         Log::debug(print_r($e->getMessage(), true));
    //         return false;
    //     } catch (Exception $e) {
    //         Log::debug('=== TaiwanPay reserve unknown error ===');
    //         Log::debug(print_r($e->getMessage(), true));
    //         return false;
    //     }
    // }


    public function checkYushanOrder($parameters)
    {
        try {
            this->baseUrl='';
            $this->headers='';
            //用formpost方式
            $this->json=false;
            //送出資料
            $response = $this->putParameters($parameters)
                            ->request('post',env('YushanQuery_url'));
            //轉換資料 ，且取出的資料式XML檔
            $result = simplexml_load_string($response->getBody()->getContents());

            return $result;
        } catch (ClientException $e) {
            Log::debug('=== YushanPay queryOrder ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== YushanPay queryOrder unknown error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        }
    }


}
