<?php
/**
 * User: lee
 * Date: 2018/12/26
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ProductWishlist;

class ProductWishlistRepository extends BaseRepository
{
    public function __construct(ProductWishlist $model)
    {
        $this->missionModel = $model;
    }

    /**
     * 取會員所有收藏
     * @param $memberId
     * @return mixed
     */
    public function allByMemberId($memberId = 0)
    {
        return $this->missionModel->with([
                                'product.specs.specPrices',
                                'product.img',
                                'menuProds.upperTag'
                            ])
                            ->where('member_id', $memberId)
                            ->get();
    }
}
