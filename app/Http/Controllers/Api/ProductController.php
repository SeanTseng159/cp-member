<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Parameter\Product\AllParameter;
use Ksd\Mediation\Parameter\Product\QueryParameter;
use Ksd\Mediation\Parameter\Product\TagsParameter;
use Ksd\Mediation\Services\ProductService;

class ProductController extends RestLaravelController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function all(Request $request)
    {
        $parameter = new AllParameter();
        $parameter->laravelRequest($request);
        return $this->success($this->productService->products($parameter));
    }

    public function tags(Request $request)
    {
        $parameter = new TagsParameter();
        $parameter->laravelRequest($request);
        return  $this->success($this->productService->products($parameter));
    }

    public function query(Request $request, $id)
    {
        $parameter = new QueryParameter();
        $parameter->laravelRequest($id, $request);
        return $this->success($this->productService->product($parameter));
    }
}
