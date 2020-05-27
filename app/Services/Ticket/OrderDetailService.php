<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\OrderDetailRepository;

class OrderDetailService extends BaseService
{
    protected $orderDetailRepository;
    protected $menuRepository;

    public function __construct(OrderDetailRepository $orderDetailRepository)
    {
        $this->orderDetailRepository = $orderDetailRepository;

    }

    public function find($id)
    {
        return $this->orderDetailRepository->find($id);

    }


    public function all()
    {
        return $this->orderDetailRepository->all();

    }

}
