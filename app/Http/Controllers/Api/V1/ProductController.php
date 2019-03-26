<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\ProductService;
use App\Parameter\Ticket\Product\QueryParameter;
use App\Result\Ticket\ProductResult;

use App\Services\MagentoProductService;
use App\Result\MagentoProductResult;

use App\Services\Ticket\KeywordService;

class ProductController extends RestLaravelController
{
    protected $productService;
    protected $magentoProductService;

    public function __construct(ProductService $productService, MagentoProductService $magentoProductService)
    {
        $this->productService = $productService;
        $this->magentoProductService = $magentoProductService;
    }

    /**
     * 根據 id 取得商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request, $id)
    {
        $parameter = new QueryParameter($request);

        if ($parameter->source === 'magento') {
            $data = $this->magentoProductService->findOnShelf(urldecode($id), $parameter->memberId);
            $result = (new MagentoProductResult)->get($data, true);
        } else {
            $data = $this->productService->findOnShelf($id, $parameter->memberId);
            $result = (new ProductResult)->get($data, true);
        }

        return $this->success($result);
    }

    /**
     * 根據 id 取得加購商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchase(Request $request, $id)
    {
        $data = $this->productService->findPurchaseOnShelf($id);
        $result = (new ProductResult)->getOnlyPurchase($data, true);

        return $this->success($result);
    }

    /**
     * 根據 組合商品(內容物) id 取得商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function findComboItem(Request $request, $id)
    {
        $parameter = new QueryParameter($request);

        $data = $this->productService->findComboItemOnShelf($id);
        $result = (new ProductResult)->getComboItem($data, true);

        return $this->success($result);
    }

    /**
     * 商品搜尋
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, KeywordService $keywordService)
    {
        $keyword = $request->input('search');
        $keyword = urldecode($keyword);

        // 關鍵字商品搜尋
        $keywordProducts = $keywordService->getProductsByKeyword($keyword);
        $resultKeywordProducts = (new ProductResult)->search($keywordProducts);

        // magento商品
        $magentoProducts = $this->magentoProductService->search($keyword);
        $resultMagentoProducts = (new MagentoProductResult)->search($magentoProducts);

        // ct_pass商品
        $products = $this->productService->search($keyword);
        $resultProducts = (new ProductResult)->all($products);

        // 合併商品
        $result = array_merge($resultKeywordProducts, $resultMagentoProducts, $resultProducts);

        // 排除重複商品
        $result = collect($result)->unique('id')->values()->all();

        return $this->success($result);
    }
}
