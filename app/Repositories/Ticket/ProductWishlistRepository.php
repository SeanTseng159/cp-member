<?php
/**
 * User: lee
 * Date: 2018/12/26
 * Time: 上午 10:03
 */

namespace App\Repositories\Ticket;

use App\Repositories\BaseRepository;
use App\Models\Ticket\ProductWishlist;
use App\Repositories\Ticket\ProductRepository;
use App\Repositories\MagentoProductRepository;

class ProductWishlistRepository extends BaseRepository
{
    public function __construct(ProductWishlist $model, ProductRepository $productRepository, MagentoProductRepository $MagentoProductRepository)
    {
        $this->model = $model;
        $this->productRepository = $productRepository;
        $this->MagentoProductRepository = $MagentoProductRepository;
    }

    /**
     * 取會員所有收藏
     * @param $memberId
     * @return mixed
     */
    public function allByMemberId($memberId = 0)
    {
        $products = $this->model->where('member_id', $memberId)->get();

        if ($products) {
            $products->transform(function ($row, $key) {
                $row->product = $this->productRepository->easyFind($row->prod_id, true);
                return $row;
            });
        }

        return $products;
    }
}
