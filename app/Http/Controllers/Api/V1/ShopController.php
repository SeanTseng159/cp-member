<?php


namespace App\Http\Controllers\Api\V1;

use App\Enum\StoreType;
use App\Services\Ticket\DiningCarCategoryService;
use App\Services\Ticket\DiningCarService;


class ShopController extends DiningCarController
{
    protected $type = StoreType::Shop;

    public function __construct(DiningCarService $service, DiningCarCategoryService $categoryService)
    {

        parent::__construct($service,$categoryService);
        $this->service->setStoreType($this->type);

    }
}
