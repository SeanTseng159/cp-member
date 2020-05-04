<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;


use App\Services\BaseService;
use App\Repositories\Ticket\SupplierRepository;


class SupplierService extends BaseService
{
    protected $repository;
    
    

    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;

    }

  
    public function easyFind($id)
    {
        return $this->repository->easyFind($id);
    }
}
