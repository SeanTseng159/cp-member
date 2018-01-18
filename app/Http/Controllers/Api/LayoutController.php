<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\LayoutService;
use Ksd\Mediation\Parameter\Layout\LayoutParameter;


class LayoutController extends RestLaravelController
{
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
        return $this->success('刷新成功');

    }


    /**
     * 清除分頁快取
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function clean($id)
    {

        $this->layoutService->clean($id);
        return $this->success('刷新成功');

    }

    /**
     * 清除主分類快取
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function mainClean($id)
    {

        $this->layoutService->mainClean($id);
        return $this->success('刷新成功');

    }

    /**
     * 清除子分類快取
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function subClean($id)
    {
        $this->layoutService->subClean($id);
        return $this->success('刷新成功');

    }


}
