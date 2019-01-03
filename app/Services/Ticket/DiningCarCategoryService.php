<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\DiningCarCategoryRepository;

class DiningCarCategoryService extends BaseService
{
    protected $repository;

    public function __construct(DiningCarCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取主分類列表
     * @return mixed
     */
    public function mainCategory()
    {
        return $this->repository->categories('main');
    }
}
