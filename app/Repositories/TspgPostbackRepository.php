<?php
/**
 * User: lee
 * Date: 2017/10/06
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use App\Models\TspgPostback;

class TspgPostbackRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new TspgPostback;
    }

    /**
     * 依據帳號,查詢使用者認証
     * @param $orderNo
     * @return mixed
     */
    public function find($orderNo)
    {

        return $this->model->whereOrderNo($orderNo)->first();
    }
}
