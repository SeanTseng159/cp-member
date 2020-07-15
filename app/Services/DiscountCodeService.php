<?php
namespace App\Services;

use App\Repositories\DiscountCodeRepository;

class DiscountCodeService
{

    protected $repository;

    public function __construct(DiscountCodeRepository $repository)
    {
        $this->repository = $repository;
    }

     /**
     * 取得所有折扣
     * @return array
     */
    public function all()
    {
        return $this->repository->all();
    }

     /**
     * 取得首購折扣
     * @return array
     */
    public function discountFirst()
    {
        return $this->repository->discountFirst();
    }

    public function getEnableDiscountByCode($code)
    {
        return $this->repository->getEnableDiscountCode($code);
    }

    /**
     * 取得商品可以用折扣
     * @return array
     */
    public function allEnableDiscountByProd($prodId)
    {
        return $this->repository->allEnableDiscountByProd($prodId);
    }
}
