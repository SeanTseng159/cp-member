<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
class MemberNotification extends Model
{
    protected $table = 'member_notification';
    protected $guarded = ['id'];
    protected $connection = 'backend';

    public function diningCar()
    {
        return $this->hasOne('App\Models\Ticket\DiningCar', 'id', 'dining_car_id')->where('status', 1);
    }

   /**
     * 取得封面圖
     */
    public function mainImg()
    {
        return $this->hasOne('App\Models\Ticket\DiningCarLogoImg','dining_car_id','dining_car_id');
    }
}
