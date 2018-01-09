<?php
/**
 * User: lee
 * Date: 2017/10/26
 * Time: 上午 9:42
 */

namespace App\Services\Ipass;

use App\Repositories\Ipass\MemberRepository;

class MemberService
{

    protected $repository;

    public function __construct(MemberRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取得授權code
     * @param $data
     * @return mixed
     */
    public function authorize($parameters)
    {
        return $this->repository->authorize($parameters);
    }

    /**
     * 取得會員資料
     * @param $data
     * @return mixed
     */
    public function member($parameters)
    {
        return $this->repository->member($parameters);
    }

    /**
     * 會員登出
     * @param $data
     * @return mixed
     */
    public function logout($parameters)
    {
        return $this->repository->logout($parameters);
    }

}
