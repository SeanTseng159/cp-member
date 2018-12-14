<?php
/**
 * User: lee
 * Date: 2018/12/05
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\PromotionRepository;

class PromotionService extends BaseService
{
    public function __construct(PromotionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取資料
     * @param $id
     * @return App\Repositories\Ticket\Promotion
     */
    public function find($id = 0)
    {
        return $this->repository->find($id);
    }
}
