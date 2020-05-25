<?php
/**
 * User: lee
 * Date: 2018/11/22
 * Time: 上午 10:03
 */

namespace App\Traits;

use App\Services\Ticket\SupplierService;




trait CartMoreHelper
{
   

    /**
     * 檢查購物車內商品狀態、金額、數量
     * @param $dining_car_id
     * @return array
     */
    public function getCartInfo($supplier_id)
    {
        $supplierService = app()->build(SupplierService::class);
        $result = $supplierService->easyFind($supplier_id);

        if(empty($result->employee->diningCar->name)){
            $name='';
        }else{
            $name=$result->employee->diningCar->name;
        }

        return $name;
    }


}
