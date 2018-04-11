<?php
/**
 * User: lee
 * Date: 2017/10/25
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
use Ksd\Mediation\Services\MemberTokenService;
use Ksd\Mediation\Services\OrderService;
use Log;

class OrderPaymentCompleteMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $memberId;
    private $orderNo;
    private $source;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($memberId, $source, $orderNo)
    {
        $this->memberId = $memberId;
        $this->source = $source;
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
        $MemberTokenService = app()->build(MemberTokenService::class);
        $orderService = app()->build(OrderService::class);

        // get member
        $member = $memberService->find($this->memberId);

        Log::info('=== 寄送訂單付款完成信 - 會員 ===');
        Log::debug(print_r($member, true));

        // 假如會員不存在，則離開
        if (!$member) return;

        // get order
        $parameters = new \stdClass;
        $parameters->id = $this->orderNo;
        $parameters->source = $this->source;
        $parameters->token = $MemberTokenService->generateToken($this->memberId);
        $order = $orderService->findOne($parameters);
        $order = (isset($order[0]) && $order[0]) ? $order[0] : null;

        Log::info('=== 寄送訂單付款完成信 - 訂單 ===');
        Log::debug(print_r($order, true));

        // 假如訂單不存或未付款完成，則離開
        if (!$order || $order->StatusCode !== '01') return;

        $recipient = [
            'email' => $member->email,
            'name' => $member->name
        ];

        $data = [
            'order' => $order,
            'url' => env('CITY_PASS_WEB') . 'zh-TW/orders'
        ];

        $mailService->send("CityPass都會通 - 訂單繳費完成通知(訂單編號：{$order->orderNo}))", $recipient, 'emails/orderPaymentComplete', $data);
    }
}
