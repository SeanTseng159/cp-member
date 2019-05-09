<?php
/**
 * User: lee
 * Date: 2019/05/09
 * Time: 上午 9:42
 */

namespace App\Repositories;

use App\Models\JoinPartner;
use Illuminate\Database\QueryException;

class JoinPartnerRepository
{
    protected $model;

    public function __construct(JoinPartner $model)
    {
        $this->model = $model;
    }

    /**
     * 新增
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        try {
            $model = new JoinPartner;
            $model->fill($data);
            $model->save();
            return $model;
        } catch (QueryException $e) {
            return false;
        }
    }
}
