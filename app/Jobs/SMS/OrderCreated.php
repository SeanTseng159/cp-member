<?php

/**
 * User: lee
 * Date: 2020/07/12
 * Time: 上午 9:42
 */

namespace App\Jobs\SMS;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Ksd\SMS\Services\EasyGoService;
use App\Services\Ticket\OrderService;
use Log;

class OrderCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $orderNo;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderNo)
    {
        $this->orderNo = $orderNo;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderService $orderService)
    {
        try {
            if (!$this->orderNo) return;

            // get order
            $order = $orderService->findByOrderNoWithGuestOrder($this->orderNo, false);

            // 假如訂單不存，則離開
            if (!$order) return;

            //發送簡訊
            $phoneNumber = $order->guestOrder->countryCode . $order->guestOrder->cellphone;
            if ($order->guestOrder->countryCode != '886') $phoneNumber = '+' . $phoneNumber;

            if ($order->order_payment_method == "211") {
                $message = sprintf(
                    "親愛的顧客，您好:\n已收到您於CityPass都會通 的訂購資訊，感謝您的訂購。\n\n" .
                        "訂單編號: %s\n訂購時間: %s\n訂單金額: %s\n\n" .
                        "本通知函只是通知您本系統已經收到您的訂購訊息、並供您再次自行核對之用，不代表交易已經確認/完成。\n\n" .
                        "為保留訂購權利，若未繳費，請儘速繳費，ATM繳費資訊如下。\n\n" .
                        "繳費銀行：台新銀行（812）\n繳費帳號：%s \n繳費期限：%s",
                    $order->order_no,
                    $order->created_at,
                    $order->order_amount,
                    $order->order_atm_virtual_account,
                    $order->order_atm_due_time
                );
            } else {
                $message = sprintf(
                    "親愛的顧客，您好:\n已收到您於CityPass都會通 的訂購資訊，感謝您的訂購。\n\n" .
                        "訂單編號: %s\n訂購時間: %s\n訂單金額: %s\n\n" .
                        "本通知函只是通知您本系統已經收到您的訂購訊息、並供您再次自行核對之用，不代表交易已經確認/完成。\n\n" .
                        "若付款方式選擇【ATM虛擬帳號】，繳款帳號與期限，請於CityPass都會通 訪客購物訂單查詢中查看。",
                    $order->order_no,
                    $order->created_at,
                    $order->order_amount
                );
            }

            $easyGoService = new EasyGoService;
            return $easyGoService->setLongFlag(true)->send($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('=== 寄送訂單成立簡訊 - 訪客 Error ===');
            Log::error($e->getMessage());
        }
    }
}
