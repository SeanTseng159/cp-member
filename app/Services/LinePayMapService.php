<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:56
 */


namespace App\Services;

use App\Repositories\LinepayStoreRepository;

class LinePayMapService
{
    private $repository;

    public function __construct(LinepayStoreRepository $linepayStoreRepository)
    {
        $this->repository = $linepayStoreRepository;
    }
    
    public function getStores($longitude, $latitude)
    {
        return $this->repository->getStores($longitude, $latitude);
    }

}

