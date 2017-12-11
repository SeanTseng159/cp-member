<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\IPassPay\Services;

use Ksd\IPassPay\Repositories\PayRepository;

class PayService
{
	protected $repository;

	public function __construct(PayRepository $repository)
    {
    	$this->repository = $repository;
    }

    /**
     * EC平台請求支付Token
     * @param $parameters
     * @return mixed
     */
    public function bindPayReq($parameters)
    {
    	$status = false;
    	$data = $this->repository->bindPayReq($parameters);

    	// 成功
        $status = ($data->rtnCode == '0');

        return ['status' => $status, 'data' => $data];
    }

    /**
     * 支付確認 (最後步驟)
     * @param $parameters
     * @return mixed
     */
    public function bindPayStatus($parameters)
    {
        $status = false;
        $data = null;

        if ($parameters) {
            $data = $this->repository->bindPayStatus($parameters);

            // 成功
            $status = ($data->rtnCode == '0');
        }

        return ['status' => $status, 'data' => $data];
    }
}
