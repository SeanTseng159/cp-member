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
use App\Services\Ticket\CreditCardService;
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
     * @param $params [memberId, orderNo, payAmount, products, device, hasLinePayApp]
     * @return mixed
     */
    public function payment($payment, $params = [])
    {
        switch ($payment['gateway']) {
            // 台新金流
            case '3':

            // 信用卡
            if ($payment['method'] === '111') {
                // 先接backend, 之後等payment gateway做好再換
                $ccParams = new \stdClass;
                $ccParams->orderNo = $params['orderNo'];
                $ccParams->device = ($params['device'] === 'ios' || $params['device'] === 'android') ? 2 : 1;
                $ccParams->source = 'ct_pass';

                $result = (new CreditCardService)->transmit($params['memberId'], $ccParams);

                if (!$result['url']) {
                    Logger::error('Credit Card back error', $result);
                    throw new CustomException('E9015');
                }

                return [
                    'orderNo' => $params['orderNo'],
                    'paymentUrl' => (object) [
                        'web' => $result['url'],
                        'app' => $result['url']
                    ]
                ];
            }
            // ATM
            elseif ($payment['method'] === '211') {
                $result = $this->tspgService->generateVirtualAccount([
                        'orderNo' => $params['orderNo'],
                        'amount' => $params['payAmount']
                    ]);
                Logger::info('atm :', $result);
                if ($result['code'] === '00000') {
                    // 更新虛擬帳號
                    $updateResult = $this->orderRepository->updateByOrderNo($params['orderNo'], [
                                'order_atm_bank_id' => $result['data']['bankId'],
                                'order_atm_virtual_account' => $result['data']['virtualAccount'],
                                'order_atm_due_time' => $result['data']['dueDate']
                        ]);

                    if (!$updateResult) {
                        Logger::error('Atm back error', $updateResult);
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
                $result = $this->linePayService->newReserve($params['orderNo'], $params['payAmount'], $params['itemsCount'], $params['device'], $hasLinePayApp);

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
