<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Models\Ticket\BaseModel;


class DiningCarDiscount extends BaseModel
{
    protected $table = 'dining_car_discount';
    protected $connection = 'backend';
    
    

    public function __construct()
    {

    }

    public function memberdiscout()
    {
        return $this->hasMany(MemberDiningCarDiscount::class,  'discount_id','id');
    }



}
