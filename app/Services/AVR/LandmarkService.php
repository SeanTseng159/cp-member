<?php
/**
 * User: Annie
 * Date: 2019/02/21
 * Time: 上午 10:03
 */

namespace App\Services\AVR;


use App\Repositories\AVR\LandmarkRepository;
use App\Services\BaseService;


class LandmarkService extends BaseService
{
    protected $repository;

    public function __construct(LandmarkRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function icons($hash = null)
    {
        return $this->repository->icons($hash);
    }

}
