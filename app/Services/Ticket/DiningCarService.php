<?php
/**
 * User: lee
 * Date: 2018/05/29
 * Time: 上午 10:03
 */

namespace App\Services\Ticket;

use App\Services\BaseService;
use App\Repositories\Ticket\DiningCarRepository;
use Carbon\Carbon;

class DiningCarService extends BaseService
{
    protected $repository;
    protected $pointRecordRepository;

    public function __construct(DiningCarRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 取列表
     * @param  $params
     * @return mixed
     */
    public function list($params = [])
    {
        return $this->repository->list($params);
    }

    /**
     * 取地圖列表
     * @param  $params
     * @return mixed
     */
    public function map($params = [])
    {
        return $this->repository->map($params);
    }

    /**
     * 取詳細
     * @param  $id
     * @return mixed
     */
    public function find($id = 0, $memberId = 0)
    {
        return $this->repository->find($id, $memberId);
    }

    /**
     * 是否為付費餐車
     * @param $id
     * @return bool
     */
    public function isPaid($id)
    {
        $diningCar = $this->repository->find($id);
        $isPaid = false;
        if ($diningCar->level >= 1 && $diningCar->expired_at >= Carbon::now()) {
            $isPaid = true;
        }
        return $isPaid;

    }

    public function getDetailUrlByShorterUrlId($shorterUrlId)
    {
        $diningCar = $this->repository->getDiningCarByShorterUrlId($shorterUrlId);
        if ($diningCar) {
            return config('app.web_url') . $diningCar->id;
        } else {
            return false;
        }
    }
}
