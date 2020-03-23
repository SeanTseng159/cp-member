<?php

/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use App\Core\Logger;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\LayoutService;
use App\Result\Ticket\LayoutResult;
use App\Result\Ticket\ProductResult;

use App\Cache\Redis;
use App\Cache\Config as CacheConfig;
use App\Cache\Key\LayoutKey;

use Exception;

use App\Parameter\Ticket\Product\SupplierParameter;

class LayoutController extends RestLaravelController
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
     * 取首頁資料
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function home(Request $request)
    {
        try {
            $result = $this->redis->remember(LayoutKey::HOME_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutService->home($this->lang);
                return (new LayoutResult)->home($data);
            });
            return $this->success($result);
        } catch (Exception $e) {
            Logger::error("LayoutController:home", $e->getMessage());
            $result = new \stdClass;
            $result->slide = [];
            $result->banner = [];
            $result->explorations = [];
            $result->customizes = [];
            $result->hasActivity = false;
            $result->activity = null;
            return $this->success($result);
        }
    }

    /**
     * 取選單資料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function menu(Request $request)
    {
        try {
            $result = $this->redis->remember(LayoutKey::MENU_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutService->menu($this->lang);
                return (new LayoutResult)->menu($data);
            });

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }

    /**
     * 取產品分類路徑
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function productPath(Request $request, $id)
    {
        $productId = $id;
        $output = [];
        $navBarPatch = [];
        $productPath = [['name' => 'Home', 'url' => env('CITY_PASS_WEB')]];

        //navbar 部份
        $navBars = $this->redis->remember(LayoutKey::MENU_KEY, CacheConfig::ONE_DAY, function () {
            $data = $this->layoutService->menu($this->lang);
            return (new LayoutResult)->menu($data);
        });
        foreach ($navBars as $navBarItem) {
            $categoryId = $navBarItem->id;
            $categoryName = $navBarItem->name;
            array_push($navBarPatch, ['name' => $categoryName, 'url' => env('CITY_PASS_WEB') . '/category/' . $categoryId]);
        }

        //productPatch
        $productTags = $this->layoutService->productCategory($productId);
        if ($productTags->isEmpty()) {
            return $this->success();
        }
        $mainCategory = $productTags[0]->category;
        array_push($productPath, ['name' =>  $mainCategory->tag_name, 'url' => env('CITY_PASS_WEB') . '/category/' .  $mainCategory->tag_id]);


        $output['navbar'] = $navBarPatch;
        $output['productPath'] = $productPath;
        return $this->success($output);
    }

    /**
     * 取選單資料
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function oneMenu(Request $request, $id)
    {
        try {
            $menus = $this->redis->remember(LayoutKey::MENU_KEY, CacheConfig::ONE_DAY, function () {
                $data = $this->layoutService->menu($this->lang);
                return (new LayoutResult)->menu($data);
            });

            $data = collect($menus)->where('id', $id);
            $result = (new LayoutResult)->oneMenu($data);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }

    /**
     * 取熱門探索分類
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function category(Request $request, $id)
    {
        try {
            $key = sprintf(LayoutKey::CATEGORY_KEY, $id);
            $result = $this->redis->remember($key, CacheConfig::ONE_DAY, function () use ($id) {
                $data = $this->layoutService->category($this->lang, $id);
                return (new LayoutResult)->category($data);
            });

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }

    /**
     * 取熱門探索分類下所有商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryProducts(Request $request, $id)
    {
        try {
            $page = $request->input('page', 1) - 1;
            $key = sprintf(LayoutKey::CATEGORY_PRODUCTS_KEY, $id);
            $data = $this->redis->remember($key, CacheConfig::ONE_DAY, function () use ($id) {
                return $this->layoutService->categoryProducts($this->lang, $id);
            });

            $result = (new LayoutResult)->categoryProducts($data, $page);

            return $this->success($result);
        } catch (Exception $e) {
            $result = new \stdClass;
            $result->total = 0;
            $result->records = [];
            return $this->success($result);
        }
    }

    /**
     * 取熱門探索子分類下所有商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subCategoryProducts(Request $request, $id)
    {
        try {
            $page = $request->input('page', 1) - 1;
            $key = sprintf(LayoutKey::SUB_CATEGORY_PRODUCTS_KEY, $id);
            $data = $this->redis->remember($key, CacheConfig::ONE_DAY, function () use ($id) {
                return $this->layoutService->subCategoryProducts($this->lang, $id);
            });

            $result = (new LayoutResult)->categoryProducts($data, $page);

            return $this->success($result);
        } catch (Exception $e) {
            $result = new \stdClass;
            $result->total = 0;
            $result->records = [];
            return $this->success($result);
        }
    }

    /**
     * 取得目標供應商的商品
     * @param Request $request
     * @param type $supplierId
     */
    public function supplier(Request $request, $supplierId)
    {
        try {
            $params = (new SupplierParameter($request))->products();
            $data = $this->layoutService->supplierProducts($supplierId, $params['page'], $params['limit']);
            $result = (new ProductResult)->supplierProducts($data);

            return $this->success($result);
        } catch (Exception $e) {
            return $this->failure('E0005', '資料無法取得');
        }
    }
}
