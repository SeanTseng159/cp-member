<?php
/**
 * User: lee
 * Date: 2019/03/15
 * Time: 上午 9:42
 */

namespace App\Jobs\DiningCar;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Hashids\Hashids;
use App\Services\Ticket\DiningCarPointService;

class ConsumeAmountExchangePoint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $member;
    private $consumeAmount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($member, $consumeAmount)
    {
        $this->member = $member;
        $this->consumeAmount = $consumeAmount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DiningCarPointService $pointService)
    {
        $consumeAmount = (new Hashids('DiningCarConsumeAmount', 16))->decode($this->consumeAmount);

        if ($consumeAmount && $consumeAmount[0] > 0) {
            $pointService->consumeAmountExchangePoint($this->member, $consumeAmount[0]);
        }
    }
}
