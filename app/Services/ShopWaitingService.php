<?php
/**
 * User: Annie
 * Date: 2019/09/24
 */

namespace App\Services;

use App\Repositories\ShopWaitingRepository;


class ShopWaitingService extends BaseService
{
    protected $repository;

    public function __construct(ShopWaitingRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }


}
