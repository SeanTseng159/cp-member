<?php
/**
 * User: lee
 * Date: 2019/01/08
 * Time: 上午 10:03
 * [餐車會員]
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\DiningCarMemberRepository;

class DiningCarMemberService extends BaseService
{
    protected $repository;

    public function __construct(DiningCarMemberRepository $repository)
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
     * 是否已成為會員
     * @param $memberId
     * @param $id
     * @return mixed
     */
    public function isMember($memberId = 0, $id = 0)
    {
        $member = $this->repository->find($memberId, $id);

        return ($member) ? true : false;
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
}
