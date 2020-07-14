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

class OrderPaymentComplete implements ShouldQueue
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

            $message = sprintf("親愛的顧客，您好:\n您於CityPass都會通的訂單 %s 已經繳費成功。 請您放心謝謝！\n\n若為ATM虛擬帳號付款，請等待一個小時，待系統和銀行端核對金額後，訂單狀態會自動更新。\n\n基於資料安全，在此不再顯示訂單明細，請於CityPass都會通 訪客購物訂單查詢中查看。", $order->order_no);

            $easyGoService = new EasyGoService;
            return $easyGoService->setLongFlag(true)->send($phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('=== 寄送訂單成立簡訊 - 訪客 Error ===');
            Log::error($e->getMessage());
        }
    }
}
