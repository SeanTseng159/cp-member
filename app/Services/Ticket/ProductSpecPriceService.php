<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;

use App\Repositories\Ticket\ProductSpecPriceRepository;



class ProductSpecPriceService extends BaseService
{
   

    public function __construct(ProductSpecPriceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 搜尋全部的商品
     * @param None
     * @return mixed
     */
    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

}
