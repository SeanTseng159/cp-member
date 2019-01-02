<?php
/**
 * User: lee
 * Date: 2019/01/02
 * Time: 上午 10:03
 */

namespace App\Models\Ticket;

use App\Models\Ticket\BaseModel;

class DiningCar extends BaseModel
{
    /**
     * 取得主分類
     */
  	public function category()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCarCategory', 'dining_car_category_id');
    }

    /**
     * 取得次分類
     */
  	public function subCategory()
    {
        return $this->belongsTo('App\Models\Ticket\DiningCarCategory', 'dining_car_sub_category_id');
    }
}
