<?php
/**
 * User: Lee
 * Date: 2018/11/20
 * Time: 上午 9:04
 */

namespace App\Services;

use App\Exceptions\CustomException;
use App\Repositories\Ticket\OrderRepository;
use Ksd\Payment\Services\LinePayService;
use Ksd\Payment\Services\TspgService;
use App\Core\Logger;

class PaymentService
{
    protected $orderRepository;
    protected $linePayService;
    protected $tspgService;

    public function __construct(OrderRepository $orderRepository, LinePayService $linePayService, TspgService $tspgService)
    {
        $this->orderRepository = $orderRepository;
        $this->linePayService = $linePayService;
        $this->tspgService = $tspgService;
    }

    /**
     * 商品加入購物車
     * @param $payment
     * @param $params [orderNo, payAmount, hasLinePayApp]
     * @return mixed
     */
    public function payment($payment, $params = [])
    {
        switch ($payment['gateway']) {
            // 台新金流
            case '3':

            // 信用卡
            if ($payment['method'] === '111') {

            }
            // ATM
            elseif ($payment['method'] === '211') {
                $result = $this->tspgService->generateVirtualAccount([
                        'orderNo' => $params['orderNo'],
                        'amount' => $params['payAmount']
                    ]);

                if ($result['code'] === '00000') {
                    // 更新虛擬帳號
                    $updateResult = $this->orderRepository->updateByOrderNo($params['orderNo'], [
                                'order_atm_bank_id' => $result['data']['bankId'],
                                'order_atm_virtual_account' => $result['data']['virtualAccount'],
                                'order_atm_due_time' => $result['data']['dueDate']
                        ]);

                    if (!$updateResult) {
                        Logger::error('atm back error', $updateResult);
                        throw new CustomException('E9008');
                    }

                    return ['orderNo' => $params['orderNo']];
                }
                else {
                    throw new CustomException('E9008');
                }
            }

            break;
            // Linepay
            case '4':
                $hasLinePayApp = $params['hasLinePayApp'] ?? false;
                $result = $this->linePayService->newReserve($params['orderNo'], $params['payAmount'], $params['products'], $params['device'], $hasLinePayApp);

                if (!$result || $result['code']!== '00000') {
                    Logger::error('Linepay back error', $result);
                    throw new CustomException('E9014');
                }

                return [
                    'orderNo' => $params['orderNo'],
                    'paymentUrl' => $result['data']['paymentUrl']
                ];
            break;
            // 無值
            default:
                throw new CustomException('E9006');
            break;
        }
    }
}
