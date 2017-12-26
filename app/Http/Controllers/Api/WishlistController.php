<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;
use Ksd\Mediation\Services\WishlistService;
use Ksd\Mediation\Parameter\Wishlist\WishlistParameter;

class WishlistController extends RestLaravelController
{
    private $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * 取得所有收藏列表
     * @param Request $request
     * @return mixed
     */
    public function items(Request $request)
    {
        return $this->success($this->wishlistService->items());
    }

    /**
     * 根據商品id 增加商品至收藏清單
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request, $id)
    {
        $parameter = new WishlistParameter();
        $parameter->laravelRequest($id, $request);
        $result = $this->wishlistService->add($parameter);
        return ($result) ? $this->success() : $this->failure('E0002', '新增失敗');
    }


    /**
     * 根據商品id 刪除收藏清單商品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $parameter = new WishlistParameter();
        $parameter->laravelRequest($request);
        $result = $this->wishlistService->delete($parameter);
        return ($result) ? $this->success() : $this->failure('E0004', '刪除失敗');
    }





}
