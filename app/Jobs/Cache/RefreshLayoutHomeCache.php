<?php

namespace App\Jobs\Cache;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Cache\Redis;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\LayoutKey;

use App\Services\Ticket\LayoutService;
use App\Result\Ticket\LayoutResult;

class RefreshLayoutHomeCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lang;
    protected $redis;
    protected $layoutService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($lang = 'zh-TW')
    {
        $this->lang = $lang;
        $this->redis = new Redis;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;

        // 首頁
        $this->redis->refesh(LayoutKey::HOME_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutService->home($this->lang);
                return (new LayoutResult)->home($data);
            });

        \Log::debug('reload layout home.');
    }
}
