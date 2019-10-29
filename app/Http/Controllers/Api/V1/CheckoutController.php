<?php
/**
 * User: lee
 * Date: 2018/11/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use App\Services\MenuOrderService;
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
    protected $menuOrderService;
    protected $paymentService;

    public function __construct(CartService $cartService,
                                MenuOrderService $menuOrderService,
                                OrderService $orderService,
                                PaymentService $paymentService)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->menuOrderService = $menuOrderService;
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


            //檢查購物車內所有狀態是否可購買
            $statusCode = $this->checkCartStatus($cart, $params->memberId);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 成立訂單
            $order = $this->orderService->create($params, $cart);
            if (!$order)
                throw new CustomException('E9001');

            // 寄送訂單成立通知信
            dispatch(new OrderCreatedMail($params->memberId, 'ct_pass', $order->order_no))->delay(5);

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
                'orderNo' => (string)$order->order_no,
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

    /**
     * 結帳
     * @param Request $request
     * @param $menuOrderNo
     * @return mixed
     */
    public function menuPayment(Request $request, $menuOrderNo)
    {
        try {

            $params = (new CheckoutParameter($request))->payment();
            $memberId = $request->memberId;

            if (!$memberId)
                throw new Exception("無會員資訊，無法線上結帳");

            // 檢查所有狀態是否可購買
            $menuOrder = $this->menuOrderService->checkOrderProdStatus($memberId, $menuOrderNo);
            if (!$menuOrder)
                throw new Exception("查無點餐單");

            if ($menuOrder->order_id)
                throw new Exception("已產生訂單，請至我的訂單查閱");

            // 成立訂單
            $orderNo = $this->menuOrderService->createOrder($params, $menuOrder);

            if (!$orderNo)
                throw new Exception('E9001');

            //寄送訂單成立通知信
            dispatch(new OrderCreatedMail($memberId, 'ct_pass', $orderNo))->delay(5);

            // 處理金流
            $payParams = [
                'memberId' => $memberId,
                'orderNo' => $orderNo,
                'payAmount' => $menuOrder->amount,
                'itemsCount' => count($menuOrder->details),
                'device' => $params->deviceName,
                'hasLinePayApp' => $params->hasLinePayApp
            ];

            //如果是信用卡付款 給前端處理
            if ($params->payment['gateway'] == '3' && $params->payment['method'] == '111') {
                $result = ['orderNo' => $orderNo];
            } else {
                $result = $this->paymentService->payment($params->payment, $payParams);
            }

            return $this->success($result);

        } catch (Exception $e) {
            Logger::error('CheckoutController::menuPayment', $e->getMessage());
            $msg = $e->getMessage();
            if ($msg[0] == 'E') {
                return $this->failureCode($msg);
            }
            return $this->failure('E9001', $e->getMessage());
        }
    }
}
