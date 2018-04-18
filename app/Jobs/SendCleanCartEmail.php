<?php
/**
 * User: lee
 * Date: 2017/10/25
 * Time: 上午 9:42
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\Member;
use App\Services\MailService;
use Ksd\Mediation\Services;
use Ksd\Mediation\Services\ServiceService;

class SendServiceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $member;
    protected $parameter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Member $member=null,$parameter)
    {
        $this->member = $member;
        $this->parameter =$parameter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MailService $mailService)
    {

        $mailService->sendCleanCart($this->member,$this->parameter);
    }
}
