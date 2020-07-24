<?php
/**
 * Created by PhpStorm.
 * User: Jim
 * Date: 2017/9/12
 * Time: 下午 02:56
 */


namespace App\Services;


use App\Repositories\GreenPointRepository;

class GreenPointService
{
    private $repository;


    public function __construct(GreenPointRepository $repository)
    {
        $this->repository=$repository;
    }

    public function check($code){
        return $this->repository->check($code);
    }

    public function update($id,$data){
        return $this->repository->update($id,$data);
    }

}

