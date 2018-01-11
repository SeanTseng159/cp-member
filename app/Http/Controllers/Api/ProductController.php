<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Product\AllParameter;
use Ksd\Mediation\Parameter\Product\QueryParameter;
use Ksd\Mediation\Parameter\Product\TagsParameter;
use Ksd\Mediation\Parameter\Product\SearchParameter;
use Ksd\Mediation\Services\ProductService;

class ProductController extends RestLaravelController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * 取得所有商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        $parameter = new AllParameter();
        $parameter->laravelRequest($request);
        $result = $this->productService->products($parameter);
        return $this->success([
            'total' => $result->total,
            'result' => $result->result
        ]);
    }

    /**
     * 根據 分類 取得商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tags(Request $request)
    {
        $parameter = new TagsParameter();
        $parameter->laravelRequest($request);
        $result = $this->productService->products($parameter);
        return $this->success([
            'total' => $result->total,
            'result' => $result->result
        ]);
    }

    /**
     * 根據 id 取得商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request, $id)
    {
        $parameter = new QueryParameter();
        $parameter->laravelRequest($id, $request);
        return $this->success($this->productService->product($parameter));
    }

    /**
     * 根據 關鍵字 取得商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $parameter = new SearchParameter();
        $parameter->laravelRequest($request);
        return $this->success($this->productService->search($parameter));
    }

    public function cleanAllProductCache(Request $request)
    {
        $this->productService->cleanAllProductCache();
        $parameter = new AllParameter();
        $parameter->laravelRequest($request);
        $this->productService->products($parameter);
        return $this->success();
    }
}
