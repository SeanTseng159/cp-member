<?php
/**
 * User: Annie
 * Date: 2019/02/22
 * Time: 上午 10:03
 */

namespace App\Models;

use App\Models\Ticket\BaseModel;
use App\Models\DiningCarDiscount;

class MemberDiningCarDiscount extends BaseModel
{
    protected $table = 'member_dining_car_discount';
    protected $connection = 'backend';
    protected $fillable = ['discount_id', 'member_id', 'qrcode', 'used_time'];


    public function __construct()
    {

    }

    public function discount()
    {
        return $this->hasOne(DiningCarDiscount::class, 'id', 'discount_id');
    }


}
