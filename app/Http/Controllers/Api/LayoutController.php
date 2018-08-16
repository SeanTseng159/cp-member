<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\LayoutService;
use Ksd\Mediation\Parameter\Layout\LayoutParameter;

use App\Services\Ticket\LayoutService as TicketLayoutService;
use App\Result\Ticket\LayoutResult;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\LayoutKey;

class LayoutController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    private $layoutService;

    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
    }

    /**
     * 取得首頁資料
     * @return \Illuminate\Http\JsonResponse
     */
    public function home()
    {
        return $this->success($this->layoutService->home());

    }


    /**
         * 取得廣告左右滿版資料
         * @return \Illuminate\Http\JsonResponse
         */
    public function ads()
    {
        return $this->success($this->layoutService->ads());

    }


    /**
         * 取得熱門探索資料
         * @return \Illuminate\Http\JsonResponse
         */
    public function exploration()
    {
        return $this->success($this->layoutService->exploration());

    }

    /**
         * 取得自訂版位資料
         * @return \Illuminate\Http\JsonResponse
         */
    public function customize()
    {
        return $this->success($this->layoutService->customize());

    }

    /**
         * 取得底部廣告Banner
         * @return \Illuminate\Http\JsonResponse
         */
    public function banner()
    {
        return $this->success($this->layoutService->banner());

    }

    /**
         * 取得標籤資料
         * @return \Illuminate\Http\JsonResponse
         */
    public function info()
    {
        return $this->success($this->layoutService->info());

    }

    /**
         * 利用目錄id取得目錄資料
         * @return \Illuminate\Http\JsonResponse
         */
        public function category(Request $request, $categoryId)
        {
            $parameter = new LayoutParameter();
            $parameter->laravelRequest($categoryId, $request);
            return $this->success($this->layoutService->category($parameter));

        }

    /**
         * 取得下拉選單資料
         * @return \Illuminate\Http\JsonResponse
         */
        public function menu(Request $request, $categoryId = 0)
        {
            $parameter = new LayoutParameter();
            $parameter->laravelRequest($categoryId, $request);
            return $this->success($this->layoutService->menu($parameter));
        }

        /**
         * 利用選單id取得商品資料
         * @return \Illuminate\Http\JsonResponse
         */
        public function maincategory(Request $request, $categoryId)
        {
            $parameter = new LayoutParameter();
            $parameter->laravelRequest($categoryId, $request);
            return $this->success($this->layoutService->maincategory($parameter));

        }

        /**
         * 利用選單id取得商品資料
         * @return \Illuminate\Http\JsonResponse
         */
        public function subcategory(Request $request, $subcategoryId)
        {
            $parameter = new LayoutParameter();
            $parameter->laravelRequest($subcategoryId, $request);
            return $this->success($this->layoutService->subcategory($parameter));

        }

    /**
     * 清除首頁快取
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanCache()
    {
        $this->layoutService->cleanCache();

        // 新的首頁api
        $redis = new \App\Cache\Redis;

        $redis->refesh(LayoutKey::HOME_KEY, CacheConfig::ONE_DAY, function () {
                $ticketLayoutService = app()->build(TicketLayoutService::class);
                $data = $ticketLayoutService->home($this->lang);
                return (new LayoutResult)->home($data);
            });

        return $this->success('刷新成功');
    }


    /**
     * 清除探索分類快取
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function clean($id)
    {
        $this->layoutService->clean($id);

        // 新的探索分類api
        $redis = new \App\Cache\Redis;

        $key = sprintf(LayoutKey::CATEGORY_KEY, $id);
        $redis->refesh($key, CacheConfig::ONE_DAY, function () use ($id) {
                $ticketLayoutService = app()->build(TicketLayoutService::class);
                $data = $ticketLayoutService->category($this->lang, $id);
                return (new LayoutResult)->category($data);
            });

        return $this->success('刷新成功');
    }

    /**
     * 清除探索分類商品快取
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function mainClean($id)
    {
        $this->layoutService->mainClean($id);

        // 新api
        $redis = new \App\Cache\Redis;

        $key = sprintf(LayoutKey::CATEGORY_PRODUCTS_KEY, $id);
        $redis->refesh($key, CacheConfig::ONE_DAY, function () use ($id) {
                $ticketLayoutService = app()->build(TicketLayoutService::class);
                return $ticketLayoutService->categoryProducts($this->lang, $id);
            });

        return $this->success('刷新成功');
    }

    /**
     * 清除探索子分類商品快取
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function subClean($id)
    {
        $this->layoutService->subClean($id);

        // 新api
        $redis = new \App\Cache\Redis;

        $key = sprintf(LayoutKey::SUB_CATEGORY_PRODUCTS_KEY, $id);
        $redis->refesh($key, CacheConfig::ONE_DAY, function () use ($id) {
                $ticketLayoutService = app()->build(TicketLayoutService::class);
                return $ticketLayoutService->subCategoryProducts($this->lang, $id);
            });

        return $this->success('刷新成功');
    }

    /**
     * 清除選單快取
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanMenu()
    {
        $this->layoutService->cleanMenu();

        // 新的選單api
        $redis = new \App\Cache\Redis;

        $menus = $redis->refesh(LayoutKey::MENU_KEY, CacheConfig::ONE_DAY, function () {
                $ticketLayoutService = app()->build(TicketLayoutService::class);
                $data = $ticketLayoutService->menu($this->lang);
                return (new LayoutResult)->menu($data);
            });

        return $this->success('刷新成功');
    }
}
