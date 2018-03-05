<?php
/**
 * User: lee
 * Date: 2018/03/05
 * Time: 上午 9:42
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use App\Services\MagentoProductService;
use App\Parameter\Magento\ProductParameter;

class MagentoProductController extends RestLaravelController
{
    private $productService;

    public function __construct(MagentoProductService $productService)
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
        $parameter = (new ProductParameter)->all($request);
        $products = $this->productService->all($parameter);
        return $this->success($products);
    }

    /**
     * 根據 id 取得商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request, $id)
    {
        return $this->success($this->productService->find($id));
    }

    /**
     * 根據 所有id 取得對應商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request)
    {
        $parameter = (new ProductParameter)->query($request);
        $products = $this->productService->query($parameter);
        return $this->success($products);
    }

    /**
     * 取得所有商品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncAll(Request $request)
    {
        $products = $this->productService->syncAll();
        return $this->success($products);
    }
}
