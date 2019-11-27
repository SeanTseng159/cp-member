<?php
/**
 * User: Lee
 * Date: 2017/11/07
 * Time: 下午2:20
 */

namespace Ksd\Payment\Services;

use Ksd\Payment\Repositories\TaiwanPayRepository;
use Ksd\Mediation\Repositories\OrderRepository;
use Ksd\Mediation\Config\ProjectConfig;

class TaiwanPayService
{
	protected $repository;
    protected $order_repository;

	public function __construct(TaiwanPayRepository $repository, OrderRepository $order_repository)
    {
    	$this->repository = $repository;
        $this->orderRepo = $order_repository;
    }

    /**
     * reserve
     * @param $orderNo
     * @param $payAmount
     * @param $itemsCount
     * @param $device
     * @param $hasLinePayApp
     * @return mixed
     */
    public function newReserve($orderNo, $payAmount, $itemsCount = 0, $device = 'web', $hasLinePayApp = false)
    {
        if ($orderNo) {
            // 導向路徑
            $successUrl = url('api/v1/taiwanpay/confirm/callback?device=' . $device);
            $cancelUrl = url('api/v1/taiwanpay/confirm/failure?device=' . $device . '&orderNo=' . $orderNo);

            // if ($hasLinePayApp) {
            //     if ($device === 'ios') {
            //         $successUrl = env('LINEPAY_ISO_REDIRECT') . 'success/' . $orderNo;
            //         $cancelUrl = env('LINEPAY_ISO_REDIRECT') . 'failure/' . $orderNo;
            //     }
            //     else if ($device === 'android') {
            //         $successUrl = env('LINEPAY_ANDROID_REDIRECT') . 'success/' . $orderNo;
            //         $cancelUrl = env('LINEPAY_ANDROID_REDIRECT') . 'failure/' . $orderNo;
            //     }
            // }

            $productName = "CityPass 商品 - 共 {$itemsCount} 項";

            $taiwanpay_reserve_params = [
                "lidm" => $orderNo,
                "productName" => $productName,
                "productImageUrl" => asset('img/icon-app.png'),
                "purchAmt" => $payAmount,
                "successUrl" =>  $successUrl,
                "cancelUrl" =>  $cancelUrl,
                "hasApp" => $hasLinePayApp
            ];

            return $this->repository->reserve($taiwanpay_reserve_params);
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
}
