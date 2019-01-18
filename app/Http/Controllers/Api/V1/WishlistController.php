<?php
/**
 * User: lee
 * Date: 2018/12/26
 * Time: 上午 10:03
 */

namespace App\Http\Controllers\Api\V1;

use Exception;
use Illuminate\Http\Request;
use Ksd\Mediation\Core\Controller\RestLaravelController;

use App\Services\Ticket\ProductWishlistService;
use App\Result\Ticket\ProductWishlistResult;

use Ksd\Mediation\Services\MemberTokenService;
use Ksd\Mediation\Magento\Wishlist as MagentoWishlist;

use App\Traits\ObjectHelper;

class WishlistController extends RestLaravelController
{
    use ObjectHelper;

    protected $serviceService;

    private $memberTokenService;

    public function __construct(ProductWishlistService $serviceService, MemberTokenService $memberTokenService)
    {
        $this->serviceService = $serviceService;

        $this->memberTokenService = $memberTokenService;
        $this->magento = new MagentoWishlist();
    }

    /**
     * 收藏列表
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function items(Request $request)
    {
        try {
            $memberId = $request->memberId;
            $wishlists = $this->serviceService->allByMemberId($memberId);
            $citypass = (new ProductWishlistResult)->all($wishlists);

            $magento = $this->magento
                            ->userAuthorization($this->memberTokenService->magentoUserToken())
                            ->items();

            if (!$magento) $magento = [];
            if (!$citypass) $citypass = [];

            $data = array_filter(array_merge($magento, $citypass));
            $result = $this->multiArraySort($data, 'addAt');

            return $this->success($result);
        } catch (Exception $e) {
            return $this->success();
        }
    }
}
