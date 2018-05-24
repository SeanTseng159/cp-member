<?php
/**
 * User: lee
 * Date: 2017/10/26
 * Time: 上午 9:42
 */

namespace App\Repositories;

use App\Models\Newsletter;
use Illuminate\Database\QueryException;

class NewsletterRepository
{
    protected $model;

    public function __construct(Newsletter $model)
    {
        $this->model = $model;
    }

    /**
     * 新增電子報名單
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $newsletter = new Newsletter();
            $newsletter->fill($data);
            $newsletter->save();
            return $newsletter;
        } catch (QueryException $e) {
            \Log::debug('=== Newsletter Create Error ===');
            \Log::debug(print_r($e->getMessage() ,true));
            return false;
        }
    }

    /**
     * 更新電子報名單資料
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        try {
            $newsletter = $this->model->find($id);

            if ($newsletter) {
                $newsletter->fill($data);
                $newsletter->save();
                return $newsletter;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 刪除電子報名單
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $newsletter = $this->model->find($id);

            if ($newsletter) {
                $newsletter->delete();
                return $id;
            } else {
                return false;
            }
        } catch (QueryException $e) {
            return false;
        }
    }

    /**
     * 依據ID查詢
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * 依據email查詢
     * @param $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->model->whereEmail($email)->first();
    }

    /**
     * 依據memberid查詢
     * @param $email
     * @return mixed
     */
    public function findByMemberId($memberId)
    {
        return $this->model->where('member_id', $memberId)->first();
    }
}
