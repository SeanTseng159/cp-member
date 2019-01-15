<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\MemberDiningCarRepository;

class MemberDiningCarService extends BaseService
{
    protected $repository;

    public function __construct(MemberDiningCarRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 新增
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function add($memberId = 0, $id = 0)
    {
        return $this->repository->add($memberId, $id);
    }

    /**
     * 刪除
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function delete($memberId = 0, $id = 0)
    {
        return $this->repository->delete($memberId, $id);
    }

    /**
     * 取單一
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function find($memberId = 0, $id = 0)
    {
        return $this->repository->find($memberId, $id);
    }

    /**
     * 是否收藏
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function isFavorite($memberId = 0, $id = 0)
    {
        $result = $this->repository->find($memberId, $id);

        return ($result) ? true : false;
    }

    /**
     * 取列表
     * @param $memberId
     * @param $params [page, limit]
     * @return mixed
     */
    public function list($memberId = 0, $params = [])
    {
        return $this->repository->list($memberId, $params);
    }

    /**
     * 依據會員取相關餐車
     * @param $memberId
     * @return mixed
     */
    public function getAllByMemberId($memberId = 0)
    {
        return $this->repository->getAllByMemberId($memberId);
    }

    /**
     * 依據會員取相關餐車分類
     * @param $memberId
     * @return mixed
     */
    public function getCategoriesByMemberId($memberId = 0)
    {
        return $this->repository->getCategoriesByMemberId($memberId);
    }
}
