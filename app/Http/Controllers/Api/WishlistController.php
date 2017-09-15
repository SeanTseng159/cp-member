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

    public function items()
    {
        return $this->success($this->wishlistService->items());
    }


    public function add(Request $request, $id)
    {
        $parameter = new WishlistParameter();
        $parameter->laravelRequest($id, $request);
        $this->wishlistService->add($parameter);
        return $this->success();
    }


    public function delete(Request $request, $id)
    {
        $parameter = new WishlistParameter();
        $parameter->laravelRequest($id, $request);
        $this->wishlistService->delete($parameter);
        return $this->success();
    }





}
