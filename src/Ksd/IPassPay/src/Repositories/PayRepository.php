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
        $response = $this->putParameters($parameters)
            ->request('POST', 'api/BindPayReq');
        $responseQueryString = urldecode($response->getBody()->getContents());

        $this->repository->update($parameters->order_id, ['bindPayReq' => $responseQueryString]);

        return $this->parseQueryString($responseQueryString);
    }

    /**
     * 支付確認 (最後步驟)
     * @param $parameters
     * @return mixed
     */
    public function bindPayStatus($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'api/bindPayStatus');
        $responseQueryString = urldecode($response->getBody()->getContents());

        $this->repository->update($parameters->order_id, ['bindPayStatus' => $responseQueryString]);

        return $this->parseQueryString($responseQueryString);
    }

    /**
     * 退款
     * @param $parameters
     * @return mixed
     */
    public function bindRefund($parameters)
    {
        $response = $this->putParameters($parameters)
            ->request('POST', 'api/BindRefund');
        $responseQueryString = urldecode($response->getBody()->getContents());

        $this->repository->update($parameters->order_id, ['bindRefund' => $responseQueryString]);

        return $this->parseQueryString($responseQueryString);
    }
}
