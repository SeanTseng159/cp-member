<?php
/**
 * User: Danny
 * Date: 2019/07/11
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

class FindFriendInvitationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $member;
    protected $parameter;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Member $member,$parameter)
    {
        $this->member = $member;
        $this->parameter = $parameter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(MailService $mailService)
    {
        $mailService->findFriendInvitationMail($this->member,$this->parameter);
    }
}
