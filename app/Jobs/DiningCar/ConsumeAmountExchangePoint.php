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

use App\Services\Ticket\DiningCarPointService;
use Cache;

class ConsumeAmountExchangePoint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $member;
    private $consumeAmount;
    private $key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($member, $consumeAmount, $key = '')
    {
        $this->member = $member;
        $this->consumeAmount = $consumeAmount;
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DiningCarPointService $pointService)
    {
        if ($this->getCache($this->key)) return;

        if ($this->member && $this->consumeAmount > 0) {
            $this->setCache($this->key);
            $pointService->consumeAmountExchangePoint($this->member, $this->consumeAmount);
        }
    }

    private function getCache($key)
    {
        $key = sprintf('ConsumeAmountExchangePoint::%s', $key);

        return (Cache::get($key)) ? true : false;
    }

    private function setCache($key)
    {
        $key = sprintf('ConsumeAmountExchangePoint::%s', $key);

        return Cache::put($key, true, 3);
    }
}
