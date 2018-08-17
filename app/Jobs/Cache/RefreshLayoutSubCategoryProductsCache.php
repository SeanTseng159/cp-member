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

class RefreshLayoutSubCategoryProductsCache implements ShouldQueue
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

        // 選單
        $menus = $this->redis->refesh(LayoutKey::MENU_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutService->menu($this->lang);
                return (new LayoutResult)->menu($data);
            });

        if ($menus) {
            foreach ($menus as $menu) {
                // 次分類
                if ($menu->items) {
                    foreach ($menu->items as $m) {
                        $id = $m->id;
                        $key = sprintf(LayoutKey::SUB_CATEGORY_PRODUCTS_KEY, $id);
                        $data = $this->redis->refesh($key, CacheConfig::ONE_DAY, function () use ($id) {
                            return $this->layoutService->subCategoryProducts($this->lang, $id);
                        });
                    }
                }
            }
        }
    }
}
