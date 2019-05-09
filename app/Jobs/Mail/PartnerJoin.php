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

use App\Services\JoinPartnerService;
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
    public function handle(JoinPartnerService $joinPartnerService)
    {
        $params = $this->params;

        $model = $joinPartnerService->create([
                                    'company' => $params['company'],
                                    'contact_window' => $params['contactWindow'],
                                    'phone' => $params['phone'],
                                    'email' => $params['email'],
                                    'message' => $params['message'],
                                    'tax_id' => $params['taxID'],
                                    'line_id' => $params['lineID']
                                ]);
        if ($model) {
            $msg = sprintf("申請成為合作廠商\n申請編號: %s\n公司全名: %s\n聯絡人: %s\n聯絡電話: %s\nE-mail: %s\n商品簡述: %s\n統一編號: %s\nLINE ID: %s", str_pad($model->id, 5, '0', STR_PAD_LEFT), $params['company'], $params['contactWindow'], $params['phone'], $params['email'], $params['message'], $params['taxID'], $params['lineID']);
            LineNotify::sendMessage(env('CUSTOMER_SERVICE_LINE_CHANNEL'), $msg);
        }
    }
}
