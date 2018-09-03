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

use App\Jobs\Cache\RefreshLayoutHomeCache;
use App\Jobs\Cache\RefreshLayoutMenuCache;
use App\Jobs\Cache\RefreshLayoutCategoryCache;
use App\Jobs\Cache\RefreshLayoutCategoryProductsCache;
use App\Jobs\Cache\RefreshLayoutSubCategoryProductsCache;

class RefreshLayoutAllCache implements ShouldQueue
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
        dispatch(new RefreshLayoutHomeCache);

        // 選單
        dispatch(new RefreshLayoutMenuCache);

        // 熱門探索分類
        dispatch(new RefreshLayoutCategoryCache);

        // 主分類
        dispatch(new RefreshLayoutCategoryProductsCache);

        // 次分類
        dispatch(new RefreshLayoutSubCategoryProductsCache);
    }
}
