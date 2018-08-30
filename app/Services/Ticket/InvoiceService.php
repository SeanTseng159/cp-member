<?php
/**
 * User: lee
 * Date: 2018/08/27
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\OrderRepository;
use App\Repositories\Ticket\OrderDetailRepository;
use App\Repositories\Ticket\OrderRefundRepository;
use App\Repositories\MemberRepository;
use App\Traits\InvoiceHelper;

class InvoiceService extends BaseService
{
    use InvoiceHelper;

    private $orderDetailRepository;
    private $orderRefundRepository;
    private $memberRepository;
    private $totalOrder = 0;

    public function __construct(OrderRepository $repository, OrderDetailRepository $orderDetailRepository, OrderRefundRepository $orderRefundRepository, MemberRepository $memberRepository)
    {
        $this->repository = $repository;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->orderRefundRepository = $orderRefundRepository;
        $this->memberRepository = $memberRepository;
    }

    /**
     * 取得所有發票
     * @return mixed
     */
    public function getInvoices()
    {
        $invoices = $this->getOrdersByNotInvoiced();
        $modifyInvoices = $this->getOrdersByModifyInvoice();
        $deleteInvoices = $this->getOrdersByDeleteInvoice();
        $total[] = $this->totalOrder;

        return array_merge($invoices, $modifyInvoices, $deleteInvoices, $total);
    }

    /**
     * 取得未開發票訂單列表
     * @return mixed
     */
    public function getOrdersByNotInvoiced()
    {
        $orders = $this->repository->getOrdersByRecipientStatus(10, 00);

        if ($orders->isEmpty()) return [];

        // 計算發票金額
        $orders = $this->calcInvoicePrice($orders, 0);

        // 產生發票格式
        $invoices = [];
        foreach ($orders as $order) {
            $order->member = $this->memberRepository->find($order->member_id);
            $invoice = $this->transMainInvoiceFormat($order, '0');
            if ($invoice) {
                $invoices[] = $invoice;

                $detailInvoices = $this->transDetailInvoiceFormat($order);
                foreach ($detailInvoices as $detailInvoice) {
                    if ($detailInvoice) $invoices[] = $detailInvoice;
                }

                $this->totalOrder++;
            }

            // 更新發票狀態
            $status = ($order->recipientAmount <= 0) ? '31' : '09';
            $this->repository->updateRecipientStatus($order->order_id, $status);
        }

        return $invoices;
    }

    /**
     * 取得待修單發票訂單列表
     * @param $status
     * @return mixed
     */
    public function getOrdersByModifyInvoice()
    {
        $orders = $this->repository->getOrdersByRecipientStatus(23, 10);

        if ($orders->isEmpty()) return [];

        // 計算發票金額
        $orders = $this->calcInvoicePrice($orders, 1);

        // 產生發票格式
        $invoices = [];
        foreach ($orders as $order) {
            $order->member = $this->memberRepository->find($order->member_id);
            $invoice = $this->transMainInvoiceFormat($order, '1');
            if ($invoice) {
                $invoices[] = $invoice;

                $detailInvoices = $this->transDetailInvoiceFormat($order);
                foreach ($detailInvoices as $detailInvoice) {
                    if ($detailInvoice) $invoices[] = $detailInvoice;
                }

                $this->totalOrder++;
            }

            // 更新發票狀態
            $this->repository->updateRecipientStatus($order->order_id, '19');
        }

        return $invoices;
    }

    /**
     * 取得待刪單發票訂單列表
     * @param $status
     * @return mixed
     */
    public function getOrdersByDeleteInvoice()
    {
        $orders = $this->repository->getOrdersByRecipientStatus(24, 20);

        if ($orders->isEmpty()) return [];

        // 計算發票金額
        // $orders = $this->calcInvoicePrice($orders, 2);

        // 產生發票格式
        $invoices = [];
        foreach ($orders as $order) {
            // 全額退款, 金額帶0
            $order->recipientAmount = 0;

            $order->member = $this->memberRepository->find($order->member_id);

            $recipientStatus = $this->getStatusIsInvalidOrDebit($order->order_paid_at);

            // 折讓需帶退貨單號
            if ($recipientStatus == 3) {
                $refund = $this->orderRefundRepository->findByOrderId($order->order_id);

                $order->refundId = ($refund) ? $refund->order_refund_custom_id : '';
            }

            $invoice = $this->transMainInvoiceFormat($order, $recipientStatus, true);
            if ($invoice) {
                $invoices[] = $invoice;

                $this->totalOrder++;
            }

            // 更新發票狀態
            $this->repository->updateRecipientStatus($order->order_id, '29');
        }

        return $invoices;
    }

    /**
     * 計算發票金額
     * @param $orders
     * @param $status [發票狀態: 0.新增 1.修單 2.刪除 3.折讓]
     * @return mixed
     */
    public function calcInvoicePrice($orders, $status = 0)
    {
        $orders->transform(function ($order) use ($status) {
            $order->recipientAmount = 0;

            // 取發票狀態
            $recipientStatus = ($status !== 0) ? $this->getStatusIsInvalidOrDebit($order->order_paid_at) : 1;

            // 計算發票金額, 但先不處理組合商品的主商品
            foreach ($order->detail as $detail) {
                if ($detail->prod_type == 2) continue;

                if ($detail->productSpecPrice->prod_spec_price_recipient_type == 0) {
                    // 免開發票 (不計算)
                    $detail->recipient_price = 0;

                    if ($status === 0) {
                        $this->orderDetailRepository->updateRecipient($detail->order_detail_id, 4, $detail->recipient_price);
                    }

                    continue;
                }
                elseif ($detail->productSpecPrice->prod_spec_price_recipient_type == 1) {
                    // 只開立手續費 (僅計算商品手續費金額)
                    if ($status !== 0 && $detail->refund_id) {
                        // 退貨
                        $detail->recipient_price = 0;
                    }
                    else {
                        $detail->recipient_price = $detail->productSpecPrice->prod_spec_price_fee;
                    }
                }
                else {
                    // 開立全額 (商品金額)
                    if ($status !== 0 && $detail->refund_id) {
                        // 退貨
                        $detail->recipient_price = 0;
                    }
                    else {
                        $detail->recipient_price = $detail->price_retail;
                    }
                }

                $this->orderDetailRepository->updateRecipient($detail->order_detail_id, $recipientStatus, $detail->recipient_price);

                // 計算訂單總金額
                $order->recipientAmount += $detail->recipient_price;
            }

            // 計算組合商品的主商品 (總和子商品的發票金額)
            foreach ($order->detail as $detail) {
                if ($detail->prod_type != 2) continue;

                if ($status !== 0 && $detail->refund_id) {
                    // 退貨
                    $detail->recipient_price = 0;
                }
                else {
                    // 找出子商品
                    $detail->recipient_price = $order->detail->where('order_detail_addnl_seq', $detail->order_detail_seq)->where('prod_type', 4)->sum('recipient_price');

                    $recipientStatus = ($detail->recipient_price <= 0) ? 4 : 1;
                }

                $this->orderDetailRepository->updateRecipient($detail->order_detail_id, $recipientStatus, $detail->recipient_price);
            }

            return $order;
        });

        return $orders;
    }
}
