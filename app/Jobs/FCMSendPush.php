<?php
/**
 * User: Danny
 * Date: 2019/08/12
 * Time: 上午 9:42
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Services\FCMService;
use Log;

class FCMSendPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $event;
    private $memberIds;
    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($event, $memberIds, $data)
    {
        $this->event = $event;
        $this->memberIds = $memberIds;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FCMService $fcmService)
    {
        Log::info('發送推播 ：'. $this->event);
        $fcmService->memberNotify($this->event, $this->memberIds, $this->data);
    }
}
