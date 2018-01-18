<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/14
 * Time: 下午 04:44
 */

namespace Ksd\Mediation\Repositories;


use Ksd\Mediation\Magento\Wishlist as MagentoWishlist;
use Ksd\Mediation\CityPass\Wishlist as CityPassWishlist;
use Ksd\Mediation\Config\ProjectConfig;
use Ksd\Mediation\Services\MemberTokenService;

class WishlistRepository extends BaseRepository
{

    private $memberTokenService;

    public function __construct(MemberTokenService $memberTokenService)
    {
        $this->magento = new MagentoWishlist();
        $this->cityPass = new cityPassWishlist();
        parent::__construct();
        $this->memberTokenService = $memberTokenService;
    }

    /**
     * 取得所有收藏列表@return mixed
     */
    public function items()
    {
        $magento = $this->magento
            ->userAuthorization($this->memberTokenService->magentoUserToken())
            ->items();
        $cityPass = $this->cityPass
            ->authorization($this->memberTokenService->cityPassUserToken())
            ->items();

        if (!$magento) $magento = [];
        if (!$cityPass) $cityPass = [];

        $data = array_filter(array_merge($magento, $cityPass));

        return ($data) ?: null;
    }

    /**
     * 根據商品id 增加商品至收藏清單
     * @param $parameter
     * @return bool
     */
    public function add($parameter)
    {
        $source = $parameter->source;
        $id = $parameter->no;
        if($source == ProjectConfig::MAGENTO) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->add($id);
        } else {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->add($parameter);
        }

    }

    /**
     * 根據商品id 刪除收藏清單商品
     * @param $parameter
     * @return bool
     */
    public function delete($parameter)
    {
         $source = $parameter->source;
        $sku = $parameter->wishlistItemId;
        if($source == ProjectConfig::MAGENTO) {
            return $this->magento->userAuthorization($this->memberTokenService->magentoUserToken())->delete($sku);
        } else {
            return $this->cityPass->authorization($this->memberTokenService->cityPassUserToken())->delete($parameter);
        }

    }




}
