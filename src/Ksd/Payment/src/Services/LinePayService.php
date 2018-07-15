<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;

use Ksd\Payment\Repositories\LinePayRepository;
use Ksd\Mediation\Repositories\OrderRepository;
use Ksd\Mediation\Config\ProjectConfig;

class LinePayService
{
	protected $repository;
    protected $order_repository;

	public function __construct(LinePayRepository $repository, OrderRepository $order_repository)
    {
    	$this->repository = $repository;
        $this->orderRepo = $order_repository;
    }

    /**
     * reserve
     * @param $parameters
     * @return mixed
     */
    public function reserve($order_info, $request)
    {
        $order_no = $order_info['orderNo'];

        $parameters = new \stdClass();
        $parameters->source = $request->source;
        $parameters->id = $order_no;
        $order = $this->orderRepo->find($parameters);

        $line_reserve_params = [
            "orderId" => $order[0]->orderNo,
            "productName" => "CityPass商品",
            "amount" => $order[0]->orderAmount,
            "successUrl" => url('api/v1/linepay/confirm/callback?device=' . $request->device),
            "cancelUrl" => url('api/v1/linepay/confirm/callback?device=' . $request->device),
        ];

    	return $this->repository->reserve($line_reserve_params);
    }

    public function feedback($parameters)
    {
        return $this->repository->feedback($parameters);
    }
}
