<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Parameter\CheckoutParameter;

use App\Services\CartService;
use App\Services\Ticket\OrderService;
use App\Services\PaymentService;

use App\Jobs\Mail\OrderCreatedMail;

use App\Traits\CartHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;

class CheckoutController extends RestLaravelController
{
    use CartHelper;

    protected $cartService;
    protected $orderService;
    protected $paymentService;

    public function __construct(CartService $cartService,
                                OrderService $orderService,
                                PaymentService $paymentService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    /**
     * 付款資訊
     * @param $request
     * @return mixed
     */
    public function info(Request $request, $orderNo)
    {
        try {
            if (!$orderNo) return $this->failureCode('E9000');
            $order = $this->orderService->findCanPay($orderNo);
            $isPhysical = ($order->order_shipment_method === 2) ? true : false;
            // 取付款方法
            $result = $this->getCheckoutInfo($isPhysical);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failureCode('E9000');
        }
    }

    /**
     * 結帳
     * @param $request
     * @return mixed
     */
    public function payment(Request $request)
    {
        try {
            $params = (new CheckoutParameter($request))->payment();

            $cart = $this->cartService->find($params->action, $params->memberId);
            $cart = unserialize($cart);

            // 檢查購物車內所有狀態是否可購買
            $statusCode = $this->checkCartStatus($params->action, $cart, $params->memberId);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 成立訂單
            $order = $this->orderService->create($params, $cart);
            if (!$order) throw new CustomException('E9001');

            // 寄送訂單成立通知信
            // dispatch(new OrderCreatedMail($params->memberId, 'ct_pass', $order->order_no))->onQueue('high')->delay(5);

            // 處理金流
            $payParams = [
                'memberId' => $params->memberId,
                'orderNo' => $order->order_no,
                'payAmount' => $order->order_amount,
                'itemsCount' => $order->order_items,
                'device' => $params->deviceName,
                'hasLinePayApp' => $params->hasLinePayApp
            ];
            $result = $this->paymentService->payment($params->payment, $payParams);

            // 刪除購物車
            $this->cartService->delete($params->action, $params->memberId);

            return $this->success($result);
        } catch (CustomException $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode($e->getMessage());
        } catch (Exception $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode('E9006');
        }
    }

    /**
     * 重新結帳
     * @param $request
     * @param $no
     * @return mixed
     */
    public function repay(Request $request, $orderNo)
    {
        try {
            if (!$orderNo) return $this->failureCode('E9016');

            // 檢查訂單是否可付款
            $order = $this->orderService->findCanPay($orderNo);
            if (!$order) return $this->failureCode('E9016');

            // 檢查付款人
            if ($order->member_id !== $request->memberId) return $this->failureCode('E9017');

            $params = (new CheckoutParameter($request))->repay();

            // 更新訂單付款資訊
            $updateResult = $this->orderService->updateForRepay($orderNo, $params);
            if (!$updateResult) return $this->failureCode('E9015');

            // 處理金流
            $payParams = [
                'memberId' => $params->memberId,
                'orderNo' => (string) $order->order_no,
                'payAmount' => $order->order_amount,
                'itemsCount' => $order->order_items,
                'device' => $params->deviceName,
                'hasLinePayApp' => $params->hasLinePayApp
            ];
            $result = $this->paymentService->payment($params->payment, $payParams);

            return $this->success($result);
        } catch (CustomException $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode($e->getMessage());
        } catch (Exception $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode('E9006');
        }
    }
}
