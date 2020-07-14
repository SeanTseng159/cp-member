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
use App\Services\Ticket\SalesRuleService;

use App\Jobs\Mail\OrderCreatedMail;
use App\Jobs\SMS\OrderCreated;

use App\Traits\CartHelper;
use App\Traits\MemberHelper;

use App\Core\Logger;
use Exception;
use App\Exceptions\CustomException;
use Ksd\Payment\Services\BlueNewPayService;


class CheckoutController extends RestLaravelController
{
    use CartHelper;
    use MemberHelper;
    protected $cartService;
    protected $orderService;
    protected $menuOrderService;
    protected $paymentService;
    protected $blueNewPayService;
    protected $salesRuleservice;

    public function __construct(CartService $cartService,
                                MenuOrderService $menuOrderService,
                                OrderService $orderService,
                                PaymentService $paymentService,
                                BlueNewPayService $blueNewPayService,
                                SalesRuleService $salesRuleservice)
    {
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->menuOrderService = $menuOrderService;
        $this->blueNewPayService = $blueNewPayService;
        $this->salesRuleservice = $salesRuleservice;
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

            // 取快取購物車
            $cartKey = ($params->action === 'guest') ? md5($request->token) : $params->memberId;
            $cart = $this->cartService->find($params->action, $cartKey);
            $cart = unserialize($cart);

            if (!$cart) throw new CustomException('E9030');


            if (!empty($params->code)) {
                // 以code取得對應優惠碼
                $discount = $this->salesRuleservice->getEnableDiscountByCode($request->input('code'));

                $cart=$this->cartService->countDiscount($cart, $discount, $this->getMemberId());
            }


            //檢查購物車內所有狀態是否可購買
            $statusCode = $this->checkCartStatus($cart, $params->memberId);
            if ($statusCode !== '00000') return $this->failureCode($statusCode);

            // 成立訂單
            $order = $this->orderService->create($params, $cart);
            if (!$order)
                throw new CustomException('E9001');

            // 寄送訂單成立通知信 (訪客寄送簡訊)
            if ($params->action === 'guest') {
                dispatch(new OrderCreated($order->order_no))->delay(10);
            }
            else {
                dispatch(new OrderCreatedMail($params->memberId, 'ct_pass', $order->order_no))->delay(10);
            }

            // 處理金流
            $payParams = [
                'memberId' => $params->memberId,
                'orderNo' => $order->order_no,
                'payAmount' => $order->order_amount,
                'itemsCount' => $order->order_items,
                'device' => $params->deviceName,
                'hasLinePayApp' => $params->hasLinePayApp
            ];
            $result = $this->paymentHandle($params, $payParams, $cartKey);

            return $this->success($result);
        } catch (CustomException $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode($e->getMessage());
        } catch (Exception $e) {
            Logger::error('payment Error', $e->getMessage());
            return $this->failureCode('E9006');
        }
    }

    // 訂單成立後，處理付款
    private function paymentHandle($params, $payParams, $cartKey)
    {
        try {
            $result = $this->paymentService->payment($params->payment, $payParams);

            // 訂單已成立，刪除購物車
            $this->cartService->delete($params->action, $cartKey);

            return $result;
        } catch (CustomException $e) {
            // 訂單已成立，刪除購物車
            $this->cartService->delete($params->action, $cartKey);

            throw new CustomException($e->getMessage());
        } catch (Exception $e) {
            // 訂單已成立，刪除購物車
            $this->cartService->delete($params->action, $cartKey);

            throw new CustomException('E9006');
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

            // 檢查付款人，訪客不檢查
            if ($order->member_id && $order->member_id !== $request->memberId) return $this->failureCode('E9017');

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

    public function merchantValidation(Request $request)
    {
        try {
            $url = $request->input('url');
            $dns = $request->input('dns');

            if (is_null($dns) || is_null($url)) {
                return $this->failureCode('E0001');
            }

            $result = $this->blueNewPayService->merchant($url, $dns);
            return $this->success($result['data']);
        } catch (Exception $e) {
            return $this->failure('E9001', $e->getMessage());
        }
    }
}
