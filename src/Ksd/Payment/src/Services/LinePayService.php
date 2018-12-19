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
            $successUrl = url('api/v1/linepay/confirm/callback?device=' . $request->device);
            $cancelUrl = url('api/v1/linepay/confirm/failure?device=' . $request->device . '&orderNo=' . $order[0]->orderNo);

            if ($hasLinePayApp) {
                if ($request->device === 'ios') {
                    $successUrl = env('LINEPAY_ISO_REDIRECT') . 'success/' . $order[0]->orderNo;
                    $cancelUrl = env('LINEPAY_ISO_REDIRECT') . 'failure/' . $order[0]->orderNo;
                }
                else if ($request->device === 'android') {
                    $successUrl = env('LINEPAY_ANDROID_REDIRECT') . 'success/' . $order[0]->orderNo;
                    $cancelUrl = env('LINEPAY_ANDROID_REDIRECT') . 'failure/' . $order[0]->orderNo;
                }
            }

            foreach ($order[0]->items as $item) {
                $productNameAry[] = $item['name'];
            }

            $productName = implode("/", $productNameAry);

            $line_reserve_params = [
                "orderId" => $order[0]->orderNo,
                "productName" => $productName,
                "productImageUrl" => asset('img/icon-app.png'),
                "amount" => $order[0]->orderAmount,
                "successUrl" =>  $successUrl,
                "cancelUrl" =>  $cancelUrl,
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
            $successUrl = url('api/v1/linepay/confirm/callback?device=' . $device);
            $cancelUrl = url('api/v1/linepay/confirm/failure?device=' . $device . '&orderNo=' . $orderNo);

            if ($hasLinePayApp) {
                if ($device === 'ios') {
                    $successUrl = env('LINEPAY_ISO_REDIRECT') . 'success/' . $orderNo;
                    $cancelUrl = env('LINEPAY_ISO_REDIRECT') . 'failure/' . $orderNo;
                }
                else if ($device === 'android') {
                    $successUrl = env('LINEPAY_ANDROID_REDIRECT') . 'success/' . $orderNo;
                    $cancelUrl = env('LINEPAY_ANDROID_REDIRECT') . 'failure/' . $orderNo;
                }
            }

            $productName = "CityPass 商品 - 共 {$itemsCount} 項";

            $line_reserve_params = [
                "orderId" => $orderNo,
                "productName" => $productName,
                "productImageUrl" => asset('img/icon-app.png'),
                "amount" => $payAmount,
                "successUrl" =>  $successUrl,
                "cancelUrl" =>  $cancelUrl,
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
}
