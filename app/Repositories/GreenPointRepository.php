<?php
/**
 * User: lee
 * Date: 2017/10/06
 * Time: 上午 9:42
 */

namespace App\Repositories;

use Illuminate\Database\QueryException;

use  App\Models\GreenPoint;

class GreenPointRepository
{
    protected $model;

    public function __construct(GreenPoint $model)
    {
        $this->model=$model;
    }

    public function check($code)
    {
        return $this->model->where('code',$code)->first();
    }

    public function update($id,$data){
        return $this->model->where('id',$id)->update($data);
    }
}
