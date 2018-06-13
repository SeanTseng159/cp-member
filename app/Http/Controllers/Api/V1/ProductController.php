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
            $data = $this->magentoProductService->findOnShelf(urldecode($id), $this->getMemberId());
            $result = (new MagentoProductResult)->get($data, true);
        } else {
            $data = $this->productService->findOnShelf($id, $parameter->memberId);
            $result = (new ProductResult)->get($data, true);
        }

        return $this->success($result);
    }
}
