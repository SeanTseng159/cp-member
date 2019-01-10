<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:56
 */


namespace App\Services;

use App\Repositories\LinepayStoreRepository;

class CheckoutService
{
    private $repository;

    public function __construct(LinepayStoreRepository $linepayStoreRepository)
    {
        $this->repository = $linepayStoreRepository;
    }

}

