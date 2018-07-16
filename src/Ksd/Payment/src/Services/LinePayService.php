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

        if ($order) {
            $hasLinePayApp = $request->hasLinePayApp;
            // 導向路徑
            $redirect = url('api/v1/linepay/confirm/callback?device=' . $request->device);
            if ($hasLinePayApp) {
                if ($request->device === 'ios') {
                    $successUrl = env('LINEPAY_ISO_REDIRECT') . 'success/' . $order[0]->orderNo;
                    $cancelUrl = env('LINEPAY_ISO_REDIRECT') . 'failure/' . $order[0]->orderNo;
                }
            }

            $line_reserve_params = [
                "orderId" => $order[0]->orderNo,
                "productName" => "CityPass商品",
                "amount" => $order[0]->orderAmount,
                "successUrl" =>  ($hasLinePayApp) ? $successUrl : $redirect,
                "cancelUrl" =>  ($hasLinePayApp) ? $cancelUrl : $redirect,
                "hasApp" => $hasLinePayApp
            ];

        	return $this->repository->reserve($line_reserve_params);
        }

        return [
                'code' => 'E0101',
                'message' => '訂單不存在'
            ];
    }

    /**
     * confirm
     * @param $parameters
     * @return mixed
     */
    public function confirm($parameters)
    {
        return $this->repository->confirm($parameters);
    }

    public function feedback($parameters)
    {
        return $this->repository->feedback($parameters);
    }
}
