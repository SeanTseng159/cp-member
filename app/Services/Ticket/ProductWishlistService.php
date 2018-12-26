<?php
/**
 * User: lee
 * Date: 2018/12/26
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\ProductWishlistRepository;

class ProductWishlistService extends BaseService
{
    public function __construct(ProductWishlistRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取會員所有收藏
     * @param $memberId
     * @return mixed
     */
    public function allByMemberId($memberId)
    {
        return $this->repository->allByMemberId($memberId);
    }
}
