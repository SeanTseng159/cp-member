<?php
/**
 * User: lee
 * Date: 2018/04/11
 * Time: 上午 9:42
 */

namespace App\Jobs\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\MailService;
use App\Services\MemberService;
use Ksd\Mediation\Services\OrderService;
use Ksd\Mediation\Magento\Order as MagentoOrder;
use Ksd\Mediation\Helper\ObjectHelper;
use Log;

class MagentoOrderATMCompleteMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ObjectHelper;

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
    public function handle()
    {
        $mailService = app()->build(MailService::class);
        $memberService = app()->build(MemberService::class);
        $magentoOrder = app()->build(MagentoOrder::class);
        $orderService = app()->build(OrderService::class);

        // get order
        $parameters = new \stdClass;
        $parameters->id = $this->orderNo;

        $order = $magentoOrder->find($parameters, false);
        Log::info('=== 寄送訂單付款完成信 - ATM訂單 ===');
        Log::debug(print_r($order, true));

        // 假如訂單不存，則離開
        if (!$order || $this->arrayDefault($order, 'status') !== 'processing') return;

        // get member
        $email = $this->arrayDefault($order, 'customer_email');
        $member = $memberService->findByEmail($email);
        if (!$member) {
            $openIdAry = explode("_", $email);
            $member = $memberService->findByOpenId($openIdAry[1], $openIdAry[0]);
        }

        Log::info('=== 寄送訂單付款完成信 - ATM會員 ===');
        Log::debug(print_r($member, true));

        // 假如會員不存在，則離開
        if (!$member) return;


        $orderNo = $this->arrayDefault($order, 'increment_id');
        $recipient = [
            'email' => $member->email,
            'name' => $member->name
        ];

        $data = [
            'orderNo' => $orderNo,
            'url' => env('CITY_PASS_WEB') . 'zh-TW/orders'
        ];

        $mailService->send("CityPass都會通 - 訂單繳費完成通知(訂單編號：{$orderNo}))", $recipient, 'emails/orderPaymentComplete', $data);
    }
}
