<?php
/**
 * Created by Fish.
 * 2019/12/19 5:53 下午
 */

namespace App\Models;

use App\Enum\MyGiftType;
use Illuminate\Database\Eloquent\Model;


class DiningCarDiscount extends Model
{
    protected $table = 'dining_car_discount';
    protected $connection = 'backend';

    public function image()
    {
        return $this->hasOne(Image::class, 'model_spec_id','id')
                    ->where('model_type',MyGiftType::DISCOUNT);

    }
}