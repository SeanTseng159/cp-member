<?php
/**
 * User: lee
 * Date: 2017/09/26
 * Time: 上午 9:42
 */

namespace App\Services;

use App\Repositories\NewsletterRepository;

class NewsletterService
{

    protected $repository;

    public function __construct(NewsletterRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 新增電子報名單
     * @param $data
     * @return \App\Models\Newsletter
     */
    public function create($data = [])
    {
        $newsletter = $this->repository->create($data);

        return $newsletter;
    }

    /**
     * 更新電子報名單資料
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * 刪除電子報名單
     * @param $id
     * @return mixed
     */
     public function delete($id)
     {
         return $this->repository->delete($id);
     }

     /**
     * 取得所有電子報名單
     * @return array
     */
    public function all()
    {
        return $this->repository->all();
    }

    /**
     * 依據ID查詢
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * 依據email查詢
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * 確認Email是否被是否被使用
     * @param $email
     * @return bool
     */
    public function checkEmailIsUse($email)
    {
        $member = $this->repository->findByEmail($email);
        return ($member);
    }

    /**
     * 依據memberid查詢
     * @param $email
     * @return mixed
     */
    public function findByMemberId($member_id)
    {
        return $this->repository->findByMemberId($member_id);
    }
}
