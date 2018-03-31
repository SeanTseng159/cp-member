<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Repositories;

use Ksd\IPassPay\Core\Client\BaseClient;
use Ksd\IPassPay\Repositories\IpasspayLogRepository;
use Ksd\IPassPay\Helper\ObjectHelper;
use GuzzleHttp\Exception\ClientException;
use Exception;
use Log;

class PayRepository extends BaseClient
{
    use ObjectHelper;

    protected $repository;

    public function __construct(IpasspayLogRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * EC平台請求支付Token (步驟一)
     * @param $parameters
     * @return mixed
     */
    public function bindPayReq($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'api/BindPayReq');

            $responseQueryString = urldecode($response->getBody()->getContents());

            $this->repository->update($parameters->order_id, ['bindPayReq' => $responseQueryString]);

            return $this->parseQueryString($responseQueryString);
        } catch (ClientException $e) {
            Log::debug('=== ipaypass api error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== ipaypass api unknown error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        }
    }

    /**
     * 支付確認 (最後步驟)
     * @param $parameters
     * @return mixed
     */
    public function bindPayStatus($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'api/bindPayStatus');
            $responseQueryString = urldecode($response->getBody()->getContents());

            $this->repository->update($parameters->order_id, ['bindPayStatus' => $responseQueryString]);

            return $this->parseQueryString($responseQueryString);
        } catch (ClientException $e) {
            Log::debug('=== ipaypass api error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== ipaypass api unknown error ===');
            return false;
        }
    }

    /**
     * 退款
     * @param $parameters
     * @return mixed
     */
    public function bindRefund($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'api/BindRefund');
            $responseQueryString = urldecode($response->getBody()->getContents());

            Log::debug('=== ipaypass refund ===');
            Log::debug(print_r($this->parseQueryString($responseQueryString), true));

            $this->repository->update($parameters->order_id, ['bindRefund' => $responseQueryString]);

            return $this->parseQueryString($responseQueryString);
        } catch (ClientException $e) {
            Log::debug('=== ipaypass api error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== ipaypass api unknown error ===');
            return false;
        }
    }

    /**
     * 交易結果查詢
     * @param $parameters
     * @return mixed
     */
    public function bindPayResult($parameters)
    {
        try {
            $response = $this->putParameters($parameters)
                ->request('POST', 'api/BindPayResult');
            $responseQueryString = urldecode($response->getBody()->getContents());

            Log::debug('=== ipaypass PayResult ===');
            Log::debug(print_r($this->parseQueryString($responseQueryString), true));

            $this->repository->update($parameters->order_id, ['bindPayResult' => $responseQueryString]);

            return $this->parseQueryString($responseQueryString);
        } catch (ClientException $e) {
            Log::debug('=== ipaypass api error ===');
            Log::debug(print_r($e->getMessage(), true));
            return false;
        } catch (Exception $e) {
            Log::debug('=== ipaypass api unknown error ===');
            return false;
        }
    }
}
