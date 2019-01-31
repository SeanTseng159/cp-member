<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\MenuCategoryRepository;

class MenuCategoryService extends BaseService
{
    protected $repository;

    public function __construct(MenuCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取列表
     * @param  $params
     * @return mixed
     */
    public function list($params = [])
    {
        return $this->repository->list($params);
    }
}
