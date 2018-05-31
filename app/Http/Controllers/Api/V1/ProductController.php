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
use App\Traits\MemberHelper;

class ProductController extends RestLaravelController
{
    use MemberHelper;

    protected $productService;
    protected $memberId;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * 根據 id 取得商品明細
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request, $id)
    {
        $data = $this->productService->find($id, $this->getMemberId());
        $result = (new ProductResult)->get($data, true);
        return $this->success($result);
    }
}
