<?php


namespace App\Http\Controllers\Api\V1;

use App\Enum\StoreType;
use App\Result\Ticket\ShopResult;
use App\Services\Ticket\DiningCarCategoryService;
use App\Services\Ticket\DiningCarService;


class ShopController extends DiningCarController
{
    protected $type = StoreType::Shop;


    public function __construct(DiningCarService $service, DiningCarCategoryService $categoryService,ShopResult $result)
    {
        parent::__construct($service, $categoryService, $result);
        $this->result = $result;
        $this->service->setStoreType($this->type);
        $this->attribute = 'shops';
    }

    //取得服務列表
    public function servicelist()
    {
    	$result=$this->result->servicelist();
    	return $this->success($result);
    	
    }

     /**
     * 取餐車列表
     *
     * @param $cars
     * @param $memberDiningCars
     * @param $lat
     *
     * @param $lng
     *
     * @return array
     */
    public function list($cars, $lat, $lng, $memberDiningCars = null)
    {
        if (!$cars) return [];

        $this->lat = $lat;
        $this->lng = $lng;
        $this->memberDiningCars = $memberDiningCars;

        $newCars = [];
        foreach ($cars as $car) {
            $newCar = $this->getCar($car);
            if ($newCar) $newCars[] = $newCar;
        }

        return $newCars;
    }


}