<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\LayoutService;
use App\Result\Ticket\LayoutResult;

use App\Cache\Redis;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\LayoutKey;

use Exception;

class CacheController extends RestLaravelController
{
    protected $lang = 'zh-TW';
    protected $layoutService;

    protected $redis;

    public function __construct(LayoutService $layoutService)
    {
        $this->layoutService = $layoutService;
        $this->redis = new Redis;
    }

    /**
     * 清除快取 (首頁)
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function home(Request $request)
    {
        // 首頁
        $this->redis->refesh(LayoutKey::HOME_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutService->home($this->lang);
                return (new LayoutResult)->home($data);
            });

        return $this->success('刷新成功');
    }

    /**
     * 清除快取 (選單)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menu(Request $request)
    {
        $this->redis->refesh(LayoutKey::MENU_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutService->menu($this->lang);
                return (new LayoutResult)->menu($data);
            });

        return $this->success('刷新成功');
    }

    /**
     * 清除快取 (熱門探索分類)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function category(Request $request, $id)
    {
        $key = sprintf(LayoutKey::CATEGORY_KEY, $id);
        $this->redis->refesh($key, CacheConfig::ONE_DAY, function () use ($id) {
            $data = $this->layoutService->category($this->lang, $id);
            return (new LayoutResult)->category($data);
        });

        return $this->success('刷新成功');
    }

    /**
     * 清除快取 (熱門探索分類下所有商品)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryProducts(Request $request, $id)
    {
        $key = sprintf(LayoutKey::CATEGORY_PRODUCTS_KEY, $id);
        $this->redis->refesh($key, CacheConfig::ONE_DAY, function () use ($id) {
            return $this->layoutService->categoryProducts($this->lang, $id);
        });

        return $this->success('刷新成功');
    }

    /**
     * 清除快取 (熱門探索子分類下所有商品)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subCategoryProducts(Request $request, $id)
    {
        $key = sprintf(LayoutKey::SUB_CATEGORY_PRODUCTS_KEY, $id);
        $data = $this->redis->refesh($key, CacheConfig::ONE_DAY, function () use ($id) {
            return $this->layoutService->subCategoryProducts($this->lang, $id);
        });

        return $this->success('刷新成功');
    }
}
