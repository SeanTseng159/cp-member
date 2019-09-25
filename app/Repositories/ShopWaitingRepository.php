<?php


namespace App\Repositories;


use App\Models\ShopWaiting;
use App\Models\Ticket\DiningCar;


class ShopWaitingRepository extends BaseRepository
{
    private $limit = 20;
    protected $model;
    protected $diningCarModel;


    public function __construct(ShopWaiting $model, DiningCar $diningCarModel)
    {
        $this->model = $model;
        $this->diningCarModel = $diningCarModel;
    }

    public function find($diningCarId)
    {
        return $this->diningCarModel->with(['waitingSetting','waitingList'])
            ->where('id',$diningCarId)
            ->first();
    }

}
