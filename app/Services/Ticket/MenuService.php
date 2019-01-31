<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\MenuRepository;

class MenuService extends BaseService
{
    protected $repository;

    public function __construct(MenuRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id = 0)
    {
        return $this->repository->find($id);
    }
}
