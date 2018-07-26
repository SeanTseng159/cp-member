<?php

namespace App\Jobs\Cache;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Cache\Redis;

class Reload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $key;
    protected $expire;
    protected $callFunction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($key, $expire, $callFunction)
    {
        $this->key = $key;
        $this->expire = $expire;
        $this->callFunction = $callFunction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Redis $redis)
    {
        $redis->refesh($this->key, $this->expire, $this->callFunction);
    }
}
