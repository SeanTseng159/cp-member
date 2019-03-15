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

use LineNotify;

class PartnerJoin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $params = $this->params;
        $msg = sprintf("申請成為合作廠商\n公司全名: %s\n聯絡人: %s\n聯絡電話: %s\nE-mail: %s\n商品簡述: %s", $params['company'], $params['contact_window'], $params['phone'], $params['email'], $params['message']);
        LineNotify::sendMessage(env('CUSTOMER_SERVICE_LINE_CHANNEL'), $msg);
    }
}
