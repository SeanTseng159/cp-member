<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheReload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $runPidName;
    protected $callClass;
    protected $callFunction;
    protected $args;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($runPidName, $callClass, $callFunction, $args)
    {
        $this->runPidName = $runPidName;
        $this->callClass = $callClass;
        $this->callFunction = $callFunction;
        $this->args = $args;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Cache::has($this->runPidName)) {
            Log::info("$this->runPidName cache has reload");
        }
        Cache::put($this->runPidName, 'true', 3600);
        try {
            $callClass = app()->build($this->callClass);
            if (empty($this->args)) {
                call_user_func([$callClass, $this->callFunction]);
            } else {
                call_user_func_array([$callClass, $this->callFunction], $this->args);
            }
        } catch (\Exception $e) {
            Log::error($e);
        }
        Cache::forget($this->runPidName);
    }
}
